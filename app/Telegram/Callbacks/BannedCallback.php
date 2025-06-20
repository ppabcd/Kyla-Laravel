<?php
namespace App\Telegram\Callbacks;
use App\Telegram\Core\BaseCallback;
use App\Telegram\Contracts\CallbackInterface;
use App\Telegram\Core\TelegramContext;
class BannedCallback extends BaseCallback implements CallbackInterface
{
    protected array $callbackNames = ['banned'];
    public function handle(\App\Telegram\Contracts\TelegramContextInterface $context): void

    {
        $context->reply(__('banned.info'));
    }
} 
