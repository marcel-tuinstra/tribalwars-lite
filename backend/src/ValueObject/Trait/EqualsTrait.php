<?php

namespace App\ValueObject\Trait;

use App\ValueObject\Interface\ValueObjectInterface;

trait EqualsTrait
{
    public static function equals(?ValueObjectInterface $left = null, ?ValueObjectInterface $right = null): bool
    {
        if (!$left || !$right) {
            return false;
        }

        if (!$left::class !== !$right::class) {
            return false;
        }

        return $left->value() === $right->value();
    }
}