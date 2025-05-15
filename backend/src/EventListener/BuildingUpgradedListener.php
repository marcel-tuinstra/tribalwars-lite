<?php

namespace App\EventListener;

use App\Event\BuildingUpgradedEvent;
use App\Service\NotificationService;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Psr\Log\LoggerInterface;

final class BuildingUpgradedListener
{
    public function __construct(
        private readonly LoggerInterface     $logger,
        private readonly NotificationService $notificationService
    )
    {
    }

    #[AsEventListener]
    public function onBuildingUpgradedEvent(BuildingUpgradedEvent $event): void
    {
        $building = $event->getBuilding();
        $village  = $building->getVillage();
        $player   = $village->getPlayer();

        $this->logger->info(sprintf(
            'Building %s upgraded to level %d (Village ID: %d)',
            $building->getCategory()->value(),
            $building->getLevel(),
            $building->getVillage()->getId()
        ));

        if ($player) {
            $this->notificationService->createNotification($player, 'building_upgraded', [
                'village'  => $village->getName(),
                'building' => $building->getCategory()->value(),
                'level'    => $building->getLevel(),
            ]);
        }
    }
}
