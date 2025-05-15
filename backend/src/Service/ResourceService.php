<?php

namespace App\Service;

use App\Collection\BuildingCollection;
use App\Entity\Village;
use App\ValueObject\Building\Category as BuildingCategory;
use App\ValueObject\Resource\Category as ResourceCategory;

class ResourceService
{
    public function produceResources(Village $village): void
    {
        $buildingCollection = new BuildingCollection($village->getBuildings()->toArray());

        $productionRates = [
            ResourceCategory::wood()->value() => 5,
            ResourceCategory::clay()->value() => 4,
            ResourceCategory::iron()->value() => 3,
        ];

        $resourceToBuildingCategory = [
            ResourceCategory::wood()->value() => BuildingCategory::lumberCamp(),
            ResourceCategory::clay()->value() => BuildingCategory::clayPit(),
            ResourceCategory::iron()->value() => BuildingCategory::ironMine(),
        ];

        foreach ($village->getResources() as $resource) {
            $baseRate = $productionRates[$resource->getCategory()->value] ?? 0;

            $buildingCategory = $resourceToBuildingCategory[$resource->getCategory()->value] ?? null;
            if ($buildingCategory === null) {
                continue;
            }

            $building = $buildingCollection->getBuildingByCategory($buildingCategory);
            $production = $baseRate * $building->getLevel();

            $resource->increaseAmount($production);
        }
    }

    public function calculateBuildingUpgradeCost(BuildingCategory $category, int $nextLevel): array
    {
        // TODO: Return resource cost based on category & level
    }

    public function calculateTroopTrainingCost(string $role, int $amount): array
    {
        // TODO: Return resource cost for training troops
    }

    public function getWarehouseCap(Village $village): int
    {
        $buildingCollection = new BuildingCollection($village->getBuildings()->toArray());
        $warehouse = $buildingCollection->getBuildingByCategory(BuildingCategory::warehouse());

        return $warehouse->getLevel() * 1000;
    }

    public function getFarmCap(Village $village): int
    {
        $buildingCollection = new BuildingCollection($village->getBuildings()->toArray());
        $farm = $buildingCollection->getBuildingByCategory(BuildingCategory::farm());

        return $farm->getLevel() * 100;
    }
}