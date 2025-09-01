<?php

namespace App\Telegram\Callbacks;

use App\Telegram\Contracts\CallbackInterface;
use App\Telegram\Core\BaseCallback;

class CancelKeyboardCallback extends BaseCallback implements CallbackInterface
{
    protected string|array $callbackName = ['cancel-keyboard'];

    public function handle(\App\Telegram\Contracts\TelegramContextInterface $context): void
    {
        $context->reply(__('keyboard.cancel'));
    }
}
