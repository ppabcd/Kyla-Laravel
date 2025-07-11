<?php
namespace App\Telegram\Callbacks;
use App\Telegram\Core\BaseCallback;
use App\Telegram\Contracts\CallbackInterface;
use App\Telegram\Core\TelegramContext;
class RejectActionMediaCallback extends BaseCallback implements CallbackInterface
{
    protected string|array $callbackName = ['reject-media'];
    public function handle(\App\Telegram\Contracts\TelegramContextInterface $context): void

    {
        $context->reply(__('media.rejected'));
    }
} 
