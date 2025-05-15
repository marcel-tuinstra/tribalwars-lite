<?php

namespace App\Collection;

use App\Entity\Building;
use App\ValueObject\Building\Category;
use Doctrine\Common\Collections\Collection;

/**
 * @extends ObjectCollection<int, Building>
 */
class BuildingCollection extends ObjectCollection implements Collection
{
    protected ?string $supportedClass = Building::class;

    /**
     * Get the first Building by Category.
     */
    public function getBuildingByCategory(Category $category): ?Building
    {
        return $this->filter(fn(Building $building) => Category::equals($building->getCategory(), $category))->first() ?: null;
    }

    /**
     * Get all completed tasks.
     */
    public function getBuildingsByLevel(int $level): self
    {
        return new self($this->filter(fn(Building $building) => $building->getLevel() === $level)->toArray());
    }

    public function sortedByLevel(): self
    {
        $buildings = $this->toArray();

        usort($buildings, function ($buildingLeft, $buildingRight) {
            return $buildingLeft->getLevel() <=> $buildingRight->getLevel();
        });

        return new self($buildings);
    }
}