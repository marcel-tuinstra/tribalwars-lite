<?php

namespace App\Event;

use App\Entity\Troop;

class TroopTrainedEvent
{
    public function __construct(
        private readonly Troop $troop
    )
    {
    }

    public function getTroop(): Troop
    {
        return $this->troop;
    }
}