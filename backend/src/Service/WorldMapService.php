<?php

namespace App\Service;

use App\Collection\VillageCollection;
use App\Entity\Player;
use App\Entity\Village;
use App\Repository\VillageRepository;

class WorldMapService
{
    public const GRID = '200';

    public function __construct(private readonly VillageRepository $villageRepository)
    {
    }

    public function getWorldSize(): array
    {
        return ['width' => self::GRID, 'height' => self::GRID];
    }

    private function getNextAvailableCoordinate(int $currentRadius, array $taken, float $fillDensity = 0.8): ?array
    {
        $center = intdiv(self::GRID, 2);
        $tries  = 10;

        while ($tries--) {
            $r     = sqrt(mt_rand() / mt_getrandmax()) * $currentRadius;
            $theta = mt_rand() / mt_getrandmax() * 2 * pi();

            $x = (int)round($center + $r * cos($theta));
            $y = (int)round($center + $r * sin($theta));

            $x = max(1, min($x, self::GRID));
            $y = max(1, min($y, self::GRID));

            if (in_array("{$x}_{$y}", $taken, true)) {
                continue;
            }

            // Now apply fill density — allow only with given chance
            if (mt_rand() / mt_getrandmax() <= $fillDensity) {
                return ['x' => $x, 'y' => $y];
            }
        }

        return null;
    }

    public function findAvailableCoordinateWithinExpandingRadius(
        int $startRadius = 10,
        int $maxRadius = 50,
        int $step = 5,
        int $triesPerRadius = 10,
        array &$taken = [],
        float $fillDensity = 0.4
    ): ?array {
        if (empty($taken)) {
            $coordsRaw = $this->villageRepository->findAllVillageCoordinates();
            $taken = array_map(fn(array $v) => "{$v['x']}_{$v['y']}", $coordsRaw);
        }

        $center = intdiv(self::GRID, 2);
        $currentRadius = $startRadius;

        while ($currentRadius <= $maxRadius) {
            $occupiedInRadius = 0;
            $totalInRadius = 0;
            $newlyAdded = 0;

            for ($x = max(1, $center - $currentRadius); $x <= min(self::GRID, $center + $currentRadius); $x++) {
                for ($y = max(1, $center - $currentRadius); $y <= min(self::GRID, $center + $currentRadius); $y++) {
                    $dist = sqrt(pow($x - $center, 2) + pow($y - $center, 2));
                    if ($dist > $currentRadius) continue;

                    $totalInRadius++;
                    if (in_array("{$x}_{$y}", $taken, true)) {
                        $occupiedInRadius++;
                    }
                }
            }

            // Scale density from low in center to high on outer edge
            $scaledDensity = $fillDensity * ($currentRadius / $maxRadius);
            $currentDensity = $occupiedInRadius / max(1, $totalInRadius);

            if ($currentDensity >= $scaledDensity) {
                $currentRadius += $step;
                continue;
            }

            $maxNewAllowed = ceil(($scaledDensity * $totalInRadius) - $occupiedInRadius);
            $tries = 0;

            while ($tries < $triesPerRadius && $newlyAdded < $maxNewAllowed) {
                $coords = $this->getNextAvailableCoordinate($currentRadius, $taken, $fillDensity);
                if ($coords) {
                    $taken[] = "{$coords['x']}_{$coords['y']}";
                    return $coords;
                }
                $tries++;
            }

            $currentRadius += $step;
        }

        return null;
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

        return array_values(array_reduce($visibleVillages, function ($carry, $village) {
            $key = $village->getX() . '_' . $village->getY();
            $carry[$key] = $village;
            return $carry;
        }, []));
    }

    private function distance(Village $villageLeft, Village $villageRight): float
    {
        return sqrt(pow($villageLeft->getX() - $villageRight->getX(), 2) + pow($villageLeft->getY() - $villageRight->getY(), 2));
    }
}