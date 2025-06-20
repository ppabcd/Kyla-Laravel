<?php
namespace App\Telegram\Callbacks;
use App\Telegram\Core\BaseCallback;
use App\Telegram\Contracts\CallbackInterface;
use App\Telegram\Core\TelegramContext;
class CancelKeyboardCallback extends BaseCallback implements CallbackInterface
{
    protected array $callbackNames = ['cancel-keyboard'];
    public function handle(\App\Telegram\Contracts\TelegramContextInterface $context): void

    {
        $context->reply(__('keyboard.cancel'));
    }
} 
