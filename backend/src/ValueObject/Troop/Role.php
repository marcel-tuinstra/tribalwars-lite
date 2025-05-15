<?php

namespace App\ValueObject\Troop;

use App\ValueObject\Interface\ValueObjectInterface;
use App\ValueObject\Trait\EqualsTrait;
use InvalidArgumentException;

final class Role implements ValueObjectInterface
{
    use EqualsTrait;

    private const ALLOWED_TYPES
        = [
            self::SPEARMAN,
            self::SWORDSMAN,
            self::SCOUT
        ];

    private const SPEARMAN  = 'spearman';
    private const SWORDSMAN = 'swordsman';
    private const SCOUT     = 'scout';

    public function __construct(private readonly string $value)
    {
        if (!in_array($value, self::ALLOWED_TYPES, true)) {
            throw new InvalidArgumentException(sprintf('Invalid type value "%s".', $value));
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public static function getValues(): array
    {
        return self::ALLOWED_TYPES;
    }

    /**
     * @return self[]
     */
    public static function getValuesAsObjects(): array
    {
        $values = [];

        foreach (self::ALLOWED_TYPES as $value) {
            $values[] = new self($value);
        }

        return $values;
    }

    // Creators
    //////////////////////////////

    public static function spearman(): self
    {
        return new self(self::SPEARMAN);
    }

    public static function swordsman(): self
    {
        return new self(self::SWORDSMAN);
    }

    public static function scout(): self
    {
        return new self(self::SCOUT);
    }
}