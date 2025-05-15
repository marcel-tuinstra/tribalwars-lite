<?php

namespace App\Utility;

use DateTime;
use DateTimeImmutable;
use DateTimeZone;

class DateTimeUtility
{
    public static function nowUtc(): DateTime
    {
        return new DateTime(null, new DateTimeZone("UTC"));
    }

    public static function nowUtcAsImmutable(): DateTimeImmutable
    {
        return new DateTimeImmutable(null, new DateTimeZone("UTC"));
    }
}