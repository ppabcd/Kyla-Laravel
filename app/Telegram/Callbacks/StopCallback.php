<?php

namespace App\Telegram\Callbacks;

use App\Services\ConversationService;
use App\Telegram\Contracts\CallbackInterface;
use App\Telegram\Contracts\TelegramContextInterface;
use App\Telegram\Core\BaseCallback;
use App\Telegram\Services\KeyboardService;

class StopCallback extends BaseCallback implements CallbackInterface
{
    protected string|array $callbackName = 'stop';

    public function __construct(
        private ConversationService $conversationService,
        private KeyboardService $keyboardService
    ) {}

    public function handle(TelegramContextInterface $context): void
    {
        $user = $context->getUser();

        if (! $user) {
            $context->answerCallbackQuery('User not found');

            return;
        }

        $success = $this->conversationService->stopConversation($context);

        if (! $success) {
            $context->sendMessage(
                __('messages.conversation.not_exists', [], $user->language_code ?? 'en'),
                ['reply_markup' => $this->keyboardService->getSearchKeyboard()]
            );
        }

        $context->answerCallbackQuery();
    }
}
