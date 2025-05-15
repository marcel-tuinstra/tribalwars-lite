<?php

namespace App\Doctrine\Type\Building;

use App\ValueObject\Building\Category;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use InvalidArgumentException;

class CategoryType extends Type
{
    const NAME = 'building:category';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return "VARCHAR(64)";
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?Category
    {
        if ($value === null) {
            return null;
        }

        try {
            return new Category($value);
        } catch (InvalidArgumentException $e) {
            throw new ConversionException(sprintf('Could not convert database value "%s" to Category value object.', $value));
        }
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof Category) {
            return $value->value();
        }

        throw new InvalidArgumentException('Invalid type value.');
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}