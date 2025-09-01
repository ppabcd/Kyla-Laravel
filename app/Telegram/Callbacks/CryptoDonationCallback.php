<?php

namespace App\Telegram\Callbacks;

use App\Telegram\Contracts\CallbackInterface;
use App\Telegram\Core\BaseCallback;

class CryptoDonationCallback extends BaseCallback implements CallbackInterface
{
    protected string|array $callbackName = ['crypto-donation'];

    public function handle(\App\Telegram\Contracts\TelegramContextInterface $context): void
    {
        $context->reply(__('messages.donation.crypto'), ['parse_mode' => 'Markdown']);
    }
}
