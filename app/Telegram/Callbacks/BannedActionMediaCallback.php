<?php

namespace App\Telegram\Callbacks;

use App\Telegram\Core\BaseCallback;

use App\Telegram\Contracts\TelegramContextInterface;

class BannedActionMediaCallback extends BaseCallback
{
    protected string $name = 'banned_action_media';

    public function handle(TelegramContextInterface $context): void
    {
        $callbackData = $context->getCallbackData();
        $parts = explode(':', $callbackData, 2);
        $action = $parts[1] ?? 'default';
        
        switch ($action) {
            case 'confirm':
                $context->reply(__('callbacks.media.banned_user'));
                break;
            case 'cancel':
                $context->reply('Aksi dibatalkan.');
                break;
            default:
                $context->reply('Aksi tidak valid.');
                break;
        }
        
        $context->answerCallbackQuery();
    }
} 
