<?php

namespace App\Telegram\Callbacks;

use App\Telegram\Commands\BalanceCommand;
use App\Telegram\Contracts\CallbackInterface;
use App\Telegram\Contracts\TelegramContextInterface;
use App\Telegram\Core\BaseCallback;

class BalanceCallback extends BaseCallback implements CallbackInterface
{
    protected string|array $callbackName = 'balance';

    public function __construct(
        private BalanceCommand $balanceCommand
    ) {}

    public function handle(TelegramContextInterface $context): void
    {
        $this->balanceCommand->handle($context);
        $context->answerCallbackQuery();
    }
}
