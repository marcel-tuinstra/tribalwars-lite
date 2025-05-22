<?php

namespace App\ValueObject\Troop;

use App\Entity\Building;
use App\ValueObject\Interface\ValueObjectInterface;
use App\ValueObject\Trait\EqualsTrait;
use InvalidArgumentException;

final class Role implements ValueObjectInterface
{
    use EqualsTrait;

    private const MILITIA    = 'militia';
    private const SCOUT      = 'scout';
    private const SPEARMAN   = 'spearman';
    private const RAIDER     = 'raider';
    private const SWORDSMAN  = 'swordsman';
    private const ARCHER     = 'archer';
    private const CAVALRY    = 'cavalry';
    private const LONGBOWMAN = 'longbowman';

    private const ALLOWED_TYPES
        = [
            self::MILITIA,
            self::SCOUT,
            self::SPEARMAN,
            self::SWORDSMAN,
            self::ARCHER,
            self::RAIDER,
            self::CAVALRY,
            self::LONGBOWMAN,
        ];

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

    /**
     * @return array<int, self[]>
     */
    /**
     * @return self[]
     */
    public static function isUnlocked(Building $building): array
    {
        $unlocked = [];

        $map = [
            1 => [self::militia(), self::scout()],
            2 => [self::spearman(), self::raider()],
            3 => [self::swordsman()],
            4 => [self::archer(), self::cavalry()],
            5 => [self::longbowman()],
        ];

        for ($i = 1; $i <= $building->getLevel(); $i++) {
            if (isset($map[$i])) {
                $unlocked = array_merge($unlocked, $map[$i]);
            }
        }

        return $unlocked;
    }

    // Creators
    //////////////////////////////

    public static function militia(): self
    {
        return new self(self::MILITIA);
    }

    public static function scout(): self
    {
        return new self(self::SCOUT);
    }

    public static function spearman(): self
    {
        return new self(self::SPEARMAN);
    }

    public static function swordsman(): self
    {
        return new self(self::SWORDSMAN);
    }

    public static function archer(): self
    {
        return new self(self::ARCHER);
    }

    public static function raider(): self
    {
        return new self(self::RAIDER);
    }

    public static function cavalry(): self
    {
        return new self(self::CAVALRY);
    }

    public static function longbowman(): self
    {
        return new self(self::LONGBOWMAN);
    }
}