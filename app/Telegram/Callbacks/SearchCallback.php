<?php

namespace App\Telegram\Callbacks;

use App\Telegram\Commands\StartCommand;
use App\Telegram\Contracts\CallbackInterface;
use App\Telegram\Contracts\TelegramContextInterface;
use App\Telegram\Core\BaseCallback;

class SearchCallback extends BaseCallback implements CallbackInterface
{
    protected string|array $callbackName = 'search';

    public function __construct(
        private StartCommand $startCommand
    ) {}

    public function handle(TelegramContextInterface $context): void
    {
        try {
            // Delegate to StartCommand since it handles search functionality
            $this->startCommand->handle($context);
        } catch (\Exception $e) {
            // Log the error for debugging
            \Illuminate\Support\Facades\Log::error('SearchCallback error: '.$e->getMessage(), [
                'user_id' => $context->getUserId(),
                'trace' => $e->getTraceAsString(),
            ]);

            // If StartCommand fails, send a user-friendly message
            $user = $context->getUser();
            $context->sendMessage(
                __('messages.error.general', [], $user->language_code ?? 'en') ?: 'An error occurred. Please try again.'
            );
        }

        $context->answerCallbackQuery();
    }
}
