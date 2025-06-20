<?php
namespace App\Telegram\Callbacks;
use App\Telegram\Core\BaseCallback;
use App\Telegram\Contracts\CallbackInterface;
use App\Telegram\Core\TelegramContext;
class ConversationCallback extends BaseCallback implements CallbackInterface
{
    protected array $callbackNames = ['conversation'];
    public function handle(\App\Telegram\Contracts\TelegramContextInterface $context): void

    {
        $context->reply(__('conversation.info'));
    }
} 
