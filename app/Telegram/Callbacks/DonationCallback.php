<?php

namespace App\Telegram\Callbacks;

use App\Telegram\Contracts\CallbackInterface;
use App\Telegram\Core\BaseCallback;

class DonationCallback extends BaseCallback implements CallbackInterface
{
    protected string|array $callbackName = ['donation'];

    public function handle(\App\Telegram\Contracts\TelegramContextInterface $context): void
    {
        $context->reply(__('messages.donation.message'), ['parse_mode' => 'Markdown']);
    }
}
