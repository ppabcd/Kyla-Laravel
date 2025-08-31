<?php

namespace App\Telegram\Callbacks;

use App\Telegram\Contracts\CallbackInterface;
use App\Telegram\Contracts\TelegramContextInterface;
use App\Telegram\Core\BaseCallback;
use App\Telegram\Services\KeyboardService;

class WaitPreferredCallback extends BaseCallback implements CallbackInterface
{
    protected string|array $callbackName = 'wait_preferred';

    public function __construct(
        private KeyboardService $keyboardService
    ) {}

    public function handle(TelegramContextInterface $context): void
    {
        $message = __('queue.continue_waiting');
        $keyboard = $this->keyboardService->getSearchingKeyboard();

        $context->editMessageText($message, $keyboard);
        $context->answerCallbackQuery(__('⏳ Continuing to wait for preferred gender'));
    }
}
