<?php

namespace App\Event;

use App\Entity\Building;

class BuildingUpgradedEvent
{
    public function __construct(
        private readonly Building $building
    )
    {
    }

    public function getBuilding(): Building
    {
        return $this->building;
    }
}