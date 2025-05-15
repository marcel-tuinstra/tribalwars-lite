<?php

namespace App\Service;

use App\Entity\Player;
use App\Entity\Village;
use App\Repository\VillageRepository;

class WorldMapService
{
    public const GRID = '100';

    public function __construct(private readonly VillageRepository $villageRepository)
    {
    }

    public function getWorldSize(): array
    {
        return ['width' => self::GRID, 'height' => self::GRID];
    }

    public function getVillageAt(int $x, int $y): ?Village
    {
        return $this->villageRepository->findOneByCoordinates($x, $y);
    }

    public function findClosestPlayerVillage(Village $village): ?Village
    {
        $playerVillages = $this->villageRepository->findAllPlayerVillages();

        usort($playerVillages, fn(Village $villageLeft, Village $villageRight) => $this->distance($village, $villageLeft) <=> $this->distance($village, $villageRight));

        return $playerVillages[0] ?? null;
    }

    public function getVisibleMapForPlayer(Player $player, int $visionRange = 5): array
    {
        $playerVillages  = $this->villageRepository->findAllVillagesByPlayer($player);
        $visibleVillages = [];

        foreach ($playerVillages as $village) {
            $nearby          = $this->villageRepository->findVillagesInRange($village->getX(), $village->getY(), $visionRange);
            $visibleVillages = array_merge($visibleVillages, $nearby);
        }

        return array_unique($visibleVillages, SORT_REGULAR);
    }

    private function distance(Village $villageLeft, Village $villageRight): float
    {
        return sqrt(pow($villageLeft->getX() - $villageRight->getX(), 2) + pow($villageLeft->getY() - $villageRight->getY(), 2));
    }
}