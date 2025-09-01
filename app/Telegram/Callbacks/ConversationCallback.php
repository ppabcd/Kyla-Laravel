<?php

namespace App\Telegram\Callbacks;

use App\Telegram\Contracts\CallbackInterface;
use App\Telegram\Core\BaseCallback;

class ConversationCallback extends BaseCallback implements CallbackInterface
{
    protected string|array $callbackName = ['conversation'];

    public function handle(\App\Telegram\Contracts\TelegramContextInterface $context): void
    {
        $context->reply(__('conversation.info'));
    }
}
