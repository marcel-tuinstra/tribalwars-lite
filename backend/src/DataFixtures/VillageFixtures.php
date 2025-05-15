<?php

namespace App\DataFixtures;

use App\Entity\Village;
use App\Entity\Building;
use App\Entity\Resource;
use App\Entity\Troop;
use App\ValueObject\Building\Category as BuildingCategory;
use App\ValueObject\Resource\Category as ResourceCategory;
use App\ValueObject\Troop\Role as TroopRole;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class VillageFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Example: create 5 bot villages
        for ($i = 0; $i < 5; $i++) {
            $village = new Village('Bot_' . $i, rand(0, 99), rand(0, 99), null);
            $this->initializeVillageDefaults($village);
            $manager->persist($village);
        }

        $manager->flush();
    }

    private function initializeVillageDefaults(Village $village): void
    {
        // Resources
        foreach (ResourceCategory::getValuesAsObjects() as $category) {
            $resource = new Resource($village, $category);
            $village->addResource($resource);
        }

        // Buildings
        foreach (BuildingCategory::getValuesAsObjects() as $category) {
            $building = new Building($village, $category);
            $village->addBuilding($building);
        }

        // Troops
        foreach (TroopRole::getValuesAsObjects() as $role) {
            $troop = new Troop($village, $role);
            $village->addTroop($troop);
        }
    }
}