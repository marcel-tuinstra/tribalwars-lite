<?php

namespace App\Service;

use App\Collection\BuildingCollection;
use App\Entity\Village;
use App\ValueObject\Building\Category as BuildingCategory;
use App\ValueObject\Resource\Category as ResourceCategory;
use App\ValueObject\Troop\Role;

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

            $building   = $buildingCollection->getBuildingByCategory($buildingCategory);
            $efficiency = 1 + ($building->getLevel() * 0.05);
            $production = $baseRate * $building->getLevel() * $efficiency;

            $resource->increaseAmount($production);
        }
    }

    public function calculateBuildingUpgradeCost(BuildingCategory $category, int $nextLevel): array
    {
        $baseCosts = [
            BuildingCategory::lumberCamp()->value() => [
                ResourceCategory::wood()->value() => 100,
                ResourceCategory::clay()->value() => 50,
                ResourceCategory::iron()->value() => 30
            ],
            BuildingCategory::clayPit()->value()    => [
                ResourceCategory::wood()->value() => 80,
                ResourceCategory::clay()->value() => 80,
                ResourceCategory::iron()->value() => 40
            ],
            BuildingCategory::ironMine()->value()   => [
                ResourceCategory::wood()->value() => 70,
                ResourceCategory::clay()->value() => 60,
                ResourceCategory::iron()->value() => 100
            ],
            BuildingCategory::barracks()->value()   => [
                ResourceCategory::wood()->value() => 200,
                ResourceCategory::clay()->value() => 150,
                ResourceCategory::iron()->value() => 100
            ],
            BuildingCategory::farm()->value()       => [
                ResourceCategory::wood()->value() => 50,
                ResourceCategory::clay()->value() => 50,
                ResourceCategory::iron()->value() => 20
            ],
            BuildingCategory::warehouse()->value()  => [
                ResourceCategory::wood()->value() => 120,
                ResourceCategory::clay()->value() => 100,
                ResourceCategory::iron()->value() => 50
            ],
        ];

        $factor = 1.5; // Scaling factor per level

        $base = $baseCosts[$category->value()] ?? [];
        $cost = [];

        foreach ($base as $resource => $amount) {
            $cost[$resource] = (int)round($amount * pow($factor, $nextLevel - 1));
        }

        return $cost;
    }

    public function calculateTroopTrainingCost(Role $role, int $amount): array
    {
        $unitCosts = [
            Role::spearman()->value()  => [
                ResourceCategory::wood()->value() => 50,
                ResourceCategory::clay()->value() => 30,
                ResourceCategory::iron()->value() => 20
            ],
            Role::swordsman()->value() => [
                ResourceCategory::wood()->value() => 30,
                ResourceCategory::clay()->value() => 50,
                ResourceCategory::iron()->value() => 40
            ],
            Role::scout()->value()     => [
                ResourceCategory::wood()->value() => 80,
                ResourceCategory::clay()->value() => 60,
                ResourceCategory::iron()->value() => 40
            ],
        ];

        $base = $unitCosts[$role->value()] ?? [];
        $cost = [];

        foreach ($base as $resource => $unitCost) {
            $cost[$resource] = $unitCost * $amount;
        }

        return $cost;
    }

    public function getWarehouseCap(Village $village): int
    {
        $buildingCollection = new BuildingCollection($village->getBuildings()->toArray());
        $warehouse          = $buildingCollection->getBuildingByCategory(BuildingCategory::warehouse());

        return $warehouse->getLevel() * 1000;
    }

    public function getFarmCap(Village $village): int
    {
        $buildingCollection = new BuildingCollection($village->getBuildings()->toArray());
        $farm               = $buildingCollection->getBuildingByCategory(BuildingCategory::farm());

        return $farm->getLevel() * 100;
    }
}