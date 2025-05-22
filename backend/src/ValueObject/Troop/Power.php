<?php

namespace App\ValueObject\Troop;

final class Power
{
    private int $attack;
    private int $defense;

    public function __construct(Role $role)
    {
        [$this->attack, $this->defense] = match ($role->value()) {
            Role::militia()->value() => [5, 5],
            Role::spearman()->value() => [4, 10],
            Role::swordsman()->value() => [6, 12],
            Role::archer()->value() => [10, 3],
            Role::raider()->value() => [3, 3],
            Role::cavalry()->value() => [8, 6],
            Role::longbowman()->value() => [12, 4],
            default => [1, 1],
        };
    }

    public function getAttack(): int
    {
        return $this->attack;
    }

    public function getDefense(): int
    {
        return $this->defense;
    }
}