<?php

namespace App\Enums;

enum BanExplicitTypeEnum: int
{
    case NON_EXPLICIT = 1;
    case EXPLICIT = 2;

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function getKeys(): array
    {
        return array_column(self::cases(), 'name');
    }
}
