<?php

namespace App\Enums;

enum SafeModeEnum: int
{
    case NOT_AVAILABLE = 0;
    case SAFE = 1;
    case UNSAFE = 2;

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
            self::NOT_AVAILABLE => 'Not Available',
            self::SAFE => 'Safe Mode',
            self::UNSAFE => 'Unsafe Mode',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::NOT_AVAILABLE => '❓',
            self::SAFE => '🛡️',
            self::UNSAFE => '⚠️',
        };
    }
}
