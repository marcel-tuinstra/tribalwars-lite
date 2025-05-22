<?php

namespace App\EventListener;

use App\Entity\Village;
use App\Event\BuildingUpgradedEvent;
use App\Service\NotificationService;
use App\Service\TickProcessor;
use App\ValueObject\Building\Category as BuildingCategory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Psr\Log\LoggerInterface;

final class BuildingUpgradedListener
{
    public function __construct(
        private readonly LoggerInterface        $logger,
        private readonly NotificationService    $notificationService,
        private readonly EntityManagerInterface $em,
    )
    {
    }

    #[AsEventListener]
    public function onBuildingUpgradedEvent(BuildingUpgradedEvent $event): void
    {
        $building = $event->getBuilding();
        $village  = $building->getVillage();
        $player   = $village->getPlayer();
        $level    = $this->calculateVillageLevel($village);

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

        $village->setLevel($level);
        $this->em->persist($village);
        $this->em->flush();

        $this->logger->info(sprintf(
            'Village [%d] is now level %d',
            $village->getId(),
            $level
        ));
    }

    private function calculateVillageLevel(Village $village): int
    {
        $weights = [
            BuildingCategory::townCenter()->value() => 2,
            BuildingCategory::lumberCamp()->value() => 1,
            BuildingCategory::miningCamp()->value() => 1,
            BuildingCategory::mill()->value()       => 1,
            BuildingCategory::barracks()->value()   => 2,
            BuildingCategory::warehouse()->value()  => 1,
        ];

        $currentScore = 0;
        $maxScore     = 0;
        $allMaxed     = true;

        foreach ($village->getBuildings() as $building) {
            $category = $building->getCategory()->value();
            if (!isset($weights[$category])) {
                continue;
            }

            $level  = $building->getLevel();
            $weight = $weights[$category];

            $currentScore += $level * $weight;
            $maxScore     += 5 * $weight;

            if ($level < 5) {
                $allMaxed = false;
            }
        }

        if ($allMaxed) {
            return 6;
        }

        if ($maxScore === 0) {
            return 1;
        }

        return max(1, (int)floor(($currentScore / $maxScore) * 5));
    }
}
