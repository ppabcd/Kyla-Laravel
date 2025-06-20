<?php

namespace App\Enums;

enum MediaTypeEnum: string
{
    case TEXT = 'text';
    case PHOTO = 'photo';
    case VIDEO = 'video';
    case VIDEO_NOTE = 'video_note';
    case VOICE = 'voice';
    case STICKER = 'sticker';
    case SPAM_ADS = 'spam_ads';

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function getKeys(): array
    {
        return array_column(self::cases(), 'name');
    }
}
