<?php

namespace App\Telegram\Callbacks;

use App\Telegram\Commands\NextCommand;
use App\Telegram\Contracts\CallbackInterface;
use App\Telegram\Contracts\TelegramContextInterface;
use App\Telegram\Core\BaseCallback;

class NextCallback extends BaseCallback implements CallbackInterface
{
    protected string|array $callbackName = 'next';

    public function __construct(
        private NextCommand $nextCommand
    ) {}

    public function handle(TelegramContextInterface $context): void
    {
        // Delegate to NextCommand
        $this->nextCommand->handle($context);
        $context->answerCallbackQuery();
    }
}
