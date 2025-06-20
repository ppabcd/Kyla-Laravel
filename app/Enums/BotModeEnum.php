<?php

namespace App\Enums;

enum BotModeEnum: int
{
    case ANONYMOUS = 0;
    case TINDER = 1;

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
            self::ANONYMOUS => 'Anonymous Mode',
            self::TINDER => 'Tinder Mode',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::ANONYMOUS => 'Chat anonymously without revealing identity',
            self::TINDER => 'Match with users like Tinder app',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::ANONYMOUS => '🕵️',
            self::TINDER => '💕',
        };
    }
}
