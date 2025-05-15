<?php

namespace App\Event;

use App\Entity\Village;

class AttackLaunchedEvent
{
    public function __construct(
        private readonly Village $attacker,
        private readonly Village $defender
    ) {}

    public function getAttacker(): Village
    {
        return $this->attacker;
    }

    public function getDefender(): Village
    {
        return $this->defender;
    }
}