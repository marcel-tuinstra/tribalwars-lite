<?php

namespace App\ValueObject\Resource;

use App\ValueObject\Interface\ValueObjectInterface;
use App\ValueObject\Trait\EqualsTrait;
use InvalidArgumentException;

final class Category implements ValueObjectInterface
{
    use EqualsTrait;

    private const ALLOWED_TYPES
        = [
            self::WOOD,
            self::STONE,
            self::FOOD
        ];

    public const WOOD       = 'wood';
    public const STONE      = 'stone';
    public const FOOD       = 'food';

    public function __construct(private readonly string $value)
    {
        if (!in_array($value, self::ALLOWED_TYPES, true)) {
            throw new InvalidArgumentException(sprintf('Invalid type value "%s".', $value));
        }
    }

    public static function fromString(string $value): self
    {
        return new self($value);
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

    public static function wood(): self
    {
        return new self(self::WOOD);
    }

    public static function stone(): self
    {
        return new self(self::STONE);
    }

    public static function food(): self
    {
        return new self(self::FOOD);
    }
}