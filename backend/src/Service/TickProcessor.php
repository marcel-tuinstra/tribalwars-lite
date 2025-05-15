<?php

namespace App\Service;

use App\Entity\Village;
use App\Event\BuildingUpgradedEvent;
use App\Event\TroopTrainedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class TickProcessor
{
    public function __construct(
        private readonly BotService               $botService,
        private readonly ResourceService          $resourceService,
        private readonly EntityManagerInterface   $em,
        private readonly EventDispatcherInterface $eventDispatcher,
    )
    {
    }

    public function processVillage(Village $village): void
    {
        $this->resourceService->produceResources($village);
        $this->processBuildingUpgrades($village);
        $this->processTroopTraining($village);

        if (!$village->isHuman()) {
            $this->botService->processBotTurn($village);
        }

        $this->em->flush();
    }

    private function processBuildingUpgrades(Village $village): void
    {
        foreach ($village->getBuildings() as $building) {
            if ($building->isUpgradeFinished()) {
                $building->completeUpgrade();

                $this->eventDispatcher->dispatch(
                    new BuildingUpgradedEvent($building)
                );
            }
        }
    }

    private function processTroopTraining(Village $village): void
    {
        foreach ($village->getTroops() as $troop) {
            if ($troop->isTrainingFinished()) {
                $troop->completeTraining();

                $this->eventDispatcher->dispatch(
                    new TroopTrainedEvent($troop)
                );
            }
        }
    }
}