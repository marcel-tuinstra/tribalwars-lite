<?php

namespace App\Collection;

use App\Entity\Village;
use Doctrine\Common\Collections\Collection;

/**
 * @extends ObjectCollection<int, Village>
 */
class VillageCollection extends ObjectCollection implements Collection
{
    protected ?string $supportedClass = Village::class;

    /**
     * Get all completed tasks.
     */
    public function getVillageByCoordinates(int $x, int $y): ?Village
    {
        return $this->filter(fn(Village $village) => $village->getX() === $x && $village->getY() === $y)->first();
    }
}