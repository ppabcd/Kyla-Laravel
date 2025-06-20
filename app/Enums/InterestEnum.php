<?php

namespace App\Enums;

enum InterestEnum: int
{
    case MALE = 1;
    case FEMALE = 2;
    case ALL = 3;

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function getKeys(): array
    {
        return array_column(self::cases(), 'name');
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::MALE => 'Interested in Male',
            self::FEMALE => 'Interested in Female',
            self::ALL => 'Interested in All',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::MALE => '♂️',
            self::FEMALE => '♀️',
            self::ALL => '👥',
        };
    }

    public function getShortLabel(): string
    {
        return match ($this) {
            self::MALE => 'Male',
            self::FEMALE => 'Female',
            self::ALL => 'All',
        };
    }
}
