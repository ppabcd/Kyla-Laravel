<?php

namespace App\Telegram\Callbacks;

use App\Telegram\Core\BaseCallback;

use App\Telegram\Contracts\TelegramContextInterface;

class EnableMediaCallback extends BaseCallback
{
    protected string|array $callbackName = 'enable_media';

    public function handle(TelegramContextInterface $context): void
    {
        $callbackData = $context->getCallbackData();
        $parts = explode(':', $callbackData, 2);
        $action = $parts[1] ?? 'default';
        
        switch ($action) {
            case 'enable':
                $context->reply(__('callbacks.media.enabled'));
                break;
            case 'disable':
                $context->reply('Media dinonaktifkan.');
                break;
            default:
                $context->reply('Aksi tidak valid.');
                break;
        }
        
        $context->answerCallbackQuery();
    }
} 
