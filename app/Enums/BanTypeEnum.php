<?php

namespace App\Enums;

enum BanTypeEnum: string
{
    case PHOTO = 'photo';
    case VIDEO = 'video';
    case VIDEO_NOTE = 'video_note';
    case STICKER = 'sticker';
    case GROUP = 'group';
    case VOICE = 'voice';
    case MANUAL = 'manual';
    case SPAM = 'spam';

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function getKeys(): array
    {
        return array_column(self::cases(), 'name');
    }
}
