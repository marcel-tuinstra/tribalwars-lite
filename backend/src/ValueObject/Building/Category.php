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
            self::TOWN_CENTER, // population
            self::LUMBER_CAMP, // wood
            self::MINING_CAMP, // stone
            self::MILL, // food
            self::BARRACKS, // unit roles
            self::WAREHOUSE // amount of resource
        ];

    private const TOWN_CENTER = 'town_center';
    private const LUMBER_CAMP = 'lumber_camp';
    private const MINING_CAMP = 'mining_camp';
    private const MILL        = 'mill';
    private const BARRACKS    = 'barracks';
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

    public static function getDefaultBuildings(): array
    {
        return array_diff(self::getValuesAsObjects(), [self::barracks(), self::warehouse()]);
    }

    // Creators
    //////////////////////////////

    public static function townCenter(): self
    {
        return new self(self::TOWN_CENTER);
    }

    public static function lumberCamp(): self
    {
        return new self(self::LUMBER_CAMP);
    }

    public static function miningCamp(): self
    {
        return new self(self::MINING_CAMP);
    }

    public static function mill(): self
    {
        return new self(self::MILL);
    }

    public static function barracks(): self
    {
        return new self(self::BARRACKS);
    }

    public static function warehouse(): self
    {
        return new self(self::WAREHOUSE);
    }
}