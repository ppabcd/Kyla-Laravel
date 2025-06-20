<?php
namespace App\Telegram\Callbacks;
use App\Telegram\Core\BaseCallback;
use App\Telegram\Contracts\CallbackInterface;
use App\Telegram\Core\TelegramContext;
class RejectActionTextCallback extends BaseCallback implements CallbackInterface
{
    protected string|array $callbackName = ['reject-text'];
    public function handle(\App\Telegram\Contracts\TelegramContextInterface $context): void

    {
        $context->reply(__('text.rejected'));
    }
} 
