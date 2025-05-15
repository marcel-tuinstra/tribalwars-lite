<?php

namespace App\ValueObject\Building;

use App\ValueObject\Interface\ValueObjectInterface;
use App\ValueObject\Trait\EqualsTrait;
use InvalidArgumentException;

final class Category implements ValueObjectInterface
{
    use EqualsTrait;

    private const ALLOWED_TYPES
        = [
            self::LUMBER_CAMP,
            self::CLAY_PIT,
            self::IRON_MINE,
            self::BARRACKS,
            self::FARM,
            self::WAREHOUSE
        ];

    private const LUMBER_CAMP = 'lumber_camp';
    private const CLAY_PIT    = 'clay_pit';
    private const IRON_MINE   = 'iron_mine';
    private const BARRACKS    = 'barracks';
    private const FARM        = 'farm';
    private const WAREHOUSE   = 'warehouse';

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

    public static function lumberCamp(): self
    {
        return new self(self::LUMBER_CAMP);
    }

    public static function clayPit(): self
    {
        return new self(self::CLAY_PIT);
    }

    public static function ironMine(): self
    {
        return new self(self::IRON_MINE);
    }

    public static function barracks(): self
    {
        return new self(self::BARRACKS);
    }

    public static function farm(): self
    {
        return new self(self::FARM);
    }

    public static function warehouse(): self
    {
        return new self(self::WAREHOUSE);
    }
}