<?php

namespace App\Service;

use App\Collection\BuildingCollection;
use App\Entity\Resource;
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
            ResourceCategory::wood()->value()  => 5,
            ResourceCategory::stone()->value() => 4,
            ResourceCategory::food()->value()  => 3,
        ];

        $resourceToBuildingCategory = [
            ResourceCategory::wood()->value()  => BuildingCategory::lumberCamp(),
            ResourceCategory::stone()->value() => BuildingCategory::miningCamp(),
            ResourceCategory::food()->value()  => BuildingCategory::mill(),
        ];

        /** @var Resource $resource */
        foreach ($village->getResources() as $resource) {
            $baseRate = $productionRates[$resource->getCategory()->value()] ?? 0;

            $buildingCategory = $resourceToBuildingCategory[$resource->getCategory()->value()] ?? null;
            if ($buildingCategory === null) {
                continue;
            }

            $building   = $buildingCollection->getBuildingByCategory($buildingCategory);
            $efficiency = 1 + ($building->getLevel() * 0.05);
            $production = $baseRate * $building->getLevel() * $efficiency;

            $resource->increaseAmount($production);

            // Cap only wood, stone, and food based on warehouse capacity
            $warehouseCap = $this->getWarehouseCap($village);

            if (in_array($resource->getCategory()->value(), [
                ResourceCategory::wood()->value(),
                ResourceCategory::stone()->value(),
                ResourceCategory::food()->value(),
            ], true)) {
                if ($resource->getAmount() > $warehouseCap) {
                    $resource->setAmount($warehouseCap);
                }
            }
        }
    }

    public function calculateBuildingUpgradeCost(BuildingCategory $category, int $nextLevel): array
    {
        $baseCosts = [
            BuildingCategory::lumberCamp()->value() => [
                ResourceCategory::wood()->value()  => 100,
                ResourceCategory::stone()->value() => 40,
                ResourceCategory::food()->value()  => 20,
            ],
            BuildingCategory::miningCamp()->value() => [
                ResourceCategory::wood()->value()  => 80,
                ResourceCategory::stone()->value() => 80,
                ResourceCategory::food()->value()  => 20,
            ],
            BuildingCategory::mill()->value() => [
                ResourceCategory::wood()->value()  => 60,
                ResourceCategory::stone()->value() => 30,
                ResourceCategory::food()->value()  => 100,
            ],
            BuildingCategory::barracks()->value() => [
                ResourceCategory::wood()->value()  => 150,
                ResourceCategory::stone()->value() => 150,
                ResourceCategory::food()->value()  => 150,
            ],
            BuildingCategory::townCenter()->value() => [
                ResourceCategory::wood()->value()  => 120,
                ResourceCategory::stone()->value() => 100,
                ResourceCategory::food()->value()  => 80,
            ],
            BuildingCategory::warehouse()->value() => [
                ResourceCategory::wood()->value()  => 100,
                ResourceCategory::stone()->value() => 60,
                ResourceCategory::food()->value()  => 40,
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
            Role::militia()->value()   => [
                ResourceCategory::wood()->value()  => 40,
                ResourceCategory::stone()->value() => 30,
                ResourceCategory::food()->value()  => 20
            ],
            Role::scout()->value()     => [
                ResourceCategory::wood()->value()  => 80,
                ResourceCategory::stone()->value() => 60,
                ResourceCategory::food()->value()  => 40
            ],
            Role::spearman()->value()  => [
                ResourceCategory::wood()->value()  => 50,
                ResourceCategory::stone()->value() => 30,
                ResourceCategory::food()->value()  => 20
            ],
            Role::swordsman()->value() => [
                ResourceCategory::wood()->value()  => 30,
                ResourceCategory::stone()->value() => 50,
                ResourceCategory::food()->value()  => 40
            ],
            Role::archer()->value()    => [
                ResourceCategory::wood()->value()  => 70,
                ResourceCategory::stone()->value() => 40,
                ResourceCategory::food()->value()  => 30
            ],
            Role::raider()->value()    => [
                ResourceCategory::wood()->value()  => 60,
                ResourceCategory::stone()->value() => 40,
                ResourceCategory::food()->value()  => 40
            ],
            Role::cavalry()->value()   => [
                ResourceCategory::wood()->value()  => 100,
                ResourceCategory::stone()->value() => 80,
                ResourceCategory::food()->value()  => 100
            ],
            Role::longbowman()->value() => [
                ResourceCategory::wood()->value()  => 120,
                ResourceCategory::stone()->value() => 60,
                ResourceCategory::food()->value()  => 80
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
        $farm               = $buildingCollection->getBuildingByCategory(BuildingCategory::townCenter());

        return $farm->getLevel() * 100;
    }
}