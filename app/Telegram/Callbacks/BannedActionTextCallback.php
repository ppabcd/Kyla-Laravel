<?php

namespace App\Telegram\Callbacks;

use App\Telegram\Contracts\CallbackInterface;
use App\Telegram\Core\BaseCallback;

class BannedActionTextCallback extends BaseCallback implements CallbackInterface
{
    protected string|array $callbackName = ['banned-text'];

    public function handle(\App\Telegram\Contracts\TelegramContextInterface $context): void
    {
        $context->reply(__('banned.text'));
    }
}
