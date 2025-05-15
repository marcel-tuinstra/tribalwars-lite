<?php

namespace App\ValueObject\Troop;

final class Power
{
    private int $attack;
    private int $defense;

    public function __construct(Role $role)
    {
        [$this->attack, $this->defense] = match ($role->value()) {
            Role::spearman()->value() => [10, 8],
            Role::swordsman()->value() => [5, 12],
            Role::scout()->value() => [1, 1],
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