<?php

namespace App\Service;

use App\Collection\BuildingCollection;
use App\Collection\ResourceCollection;
use App\Entity\Village;
use App\ValueObject\Building\Category as BuildingCategory;
use App\ValueObject\Resource\Category as ResourceCategory;
use App\Event\AttackLaunchedEvent;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class BotService
{
    public function __construct(
        private readonly WorldMapService          $worldMapService,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly EntityManagerInterface   $em
    )
    {
    }

    private array $tribePrefixes = ['Zan', 'Kor', 'Nok', 'Ash', 'Dar', 'Rak', 'Tal', 'Vor', 'Kel', 'Bar', 'Uld', 'Mak', 'Yar', 'Tor', 'Gur'];

    private array $tribeSuffixes = ['dun', 'rak', 'mir', 'tok', 'ven', 'gar', 'lun', 'zor', 'han', 'gul', 'tam', 'vok', 'tash'];

    public function createVillage(array &$taken = []): ?Village
    {
        $coords = $this->worldMapService->findAvailableCoordinateWithinExpandingRadius(taken: $taken);

        if (!$coords) {
            return null;
        }

        $prefix  = $this->tribePrefixes[array_rand($this->tribePrefixes)];
        $suffix  = $this->tribeSuffixes[array_rand($this->tribeSuffixes)];
        $village = new Village($prefix . $suffix, $coords['x'], $coords['y']);
        $this->em->persist($village);

        return $village;
    }

    public function processBotTurn(Village $village): void
    {
        $this->handleBuildingUpgrade($village);
        $this->handleTroopTraining($village);
        $this->handleAttack($village);
    }

    private function handleBuildingUpgrade(Village $village): void
    {
        $priority = [
            BuildingCategory::lumbercamp(),
            BuildingCategory::claypit(),
            BuildingCategory::ironmine(),
            BuildingCategory::warehouse(),
            BuildingCategory::farm(),
            BuildingCategory::barracks(),
        ];

        $buildingCollection = new BuildingCollection($village->getBuildings()->toArray());
        foreach ($priority as $category) {
            $building = $buildingCollection->getBuildingByCategory($category);

            if ($building->getUpgradeFinishAt() === null) {
                $building->startUpgrade(60); // Base time
                $this->em->persist($building);
                return; // Only one upgrade per turn
            }
        }
    }

    private function handleTroopTraining(Village $village): void
    {
        $buildingCollection = new BuildingCollection($village->getBuildings()->toArray());
        $resourceCollection = new ResourceCollection($village->getResources()->toArray());
        $barracks           = $buildingCollection->getBuildingByCategory(BuildingCategory::barracks());
        if ($barracks->getLevel() === 0) return;

        foreach ($village->getTroops() as $troop) {
            if ($troop->getTrainingFinishAt() !== null) continue;

            $population = $resourceCollection->getResourceByCategory(ResourceCategory::population());
            $popCap     = $this->calculatePopulationCap($village);

            if ($population->getAmount() >= $popCap) return;

            $trainable = min(10, $popCap - $population->getAmount());
            $troop->startTraining($trainable, 90, $barracks->getLevel());
            $this->em->persist($troop);
            return;
        }
    }

    private function handleAttack(Village $village): void
    {
        $target = $this->worldMapService->findClosestPlayerVillage($village);

        if ($target) {
            $this->eventDispatcher->dispatch(new AttackLaunchedEvent($village, $target));
        }
    }

    private function calculatePopulationCap(Village $village): int
    {
        $buildingCollection = new BuildingCollection($village->getBuildings()->toArray());
        $farm               = $buildingCollection->getBuildingByCategory(BuildingCategory::farm());

        return $farm->getLevel() * 100;
    }
}