<?php

namespace App\DataFixtures;

use App\Entity\Village;
use App\Service\WorldMapService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class VillageFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Example: create 5 bot villages
        for ($i = 0; $i < 5; $i++) {
            $village = new Village('Bot_' . $i, rand(1, WorldMapService::GRID), rand(1, WorldMapService::GRID));
            $manager->persist($village);
        }

        $manager->flush();
    }
}