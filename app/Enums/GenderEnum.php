<?php

namespace App\Enums;

enum GenderEnum: int
{
    case MALE = 1;
    case FEMALE = 2;

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function getKeys(): array
    {
        return array_column(self::cases(), 'name');
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::MALE => '♂️',
            self::FEMALE => '♀️',
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::MALE => 'Male',
            self::FEMALE => 'Female',
        };
    }
}
