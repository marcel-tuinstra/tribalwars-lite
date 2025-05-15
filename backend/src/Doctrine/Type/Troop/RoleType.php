<?php

namespace App\Doctrine\Type\Troop;

use App\ValueObject\Troop\Role;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use InvalidArgumentException;

class RoleType extends Type
{
    const NAME = 'troop:role';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return "VARCHAR(64)";
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?Role
    {
        if ($value === null) {
            return null;
        }

        try {
            return new Role($value);
        } catch (InvalidArgumentException $e) {
            throw new ConversionException(sprintf('Could not convert database value "%s" to Role value object.', $value));
        }
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof Role) {
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