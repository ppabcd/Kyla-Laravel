<?php
namespace App\Telegram\Callbacks;
use App\Telegram\Core\BaseCallback;
use App\Telegram\Contracts\CallbackInterface;
use App\Telegram\Core\TelegramContext;
class RetrySubscribeCheckCallback extends BaseCallback implements CallbackInterface
{
    protected string|array $callbackName = ['retry-subscribe'];
    public function handle(\App\Telegram\Contracts\TelegramContextInterface $context): void

    {
        $context->reply(__('subscribe.retry'));
    }
} 
