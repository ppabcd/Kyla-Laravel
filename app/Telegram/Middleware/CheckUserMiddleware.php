<?php

namespace App\Telegram\Middleware;

use App\Domain\Repositories\UserRepositoryInterface;
use App\Telegram\Contracts\TelegramContextInterface;
use App\Telegram\Services\KeyboardService;

class CheckUserMiddleware implements MiddlewareInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private KeyboardService $keyboardService
    ) {}

    public function handle(TelegramContextInterface $context, callable $next): void
    {
        $telegramUserId = null;

        // Get telegram user ID from message or callback query
        if ($message = $context->getMessage()) {
            $telegramUserId = $message['from']['id'] ?? null;
        } elseif ($callbackQuery = $context->getCallbackQuery()) {
            $telegramUserId = $callbackQuery['from']['id'] ?? null;
        }

        if (! $telegramUserId) {
            // No telegram user ID found, skip this middleware
            $next($context);

            return;
        }

        // Check if user exists
        $user = $this->userRepository->findByTelegramId($telegramUserId);

        if (! $user) {
            // For callback queries related to language selection, let the callback handle user creation
            if ($callbackQuery = $context->getCallbackQuery()) {
                $callbackData = $callbackQuery['data'] ?? '';
                if (str_starts_with($callbackData, 'lang-')) {
                    // Skip welcome message for language callbacks, let callback handle it
                    $next($context);

                    return;
                }
            }

            // User doesn't exist, send welcome message
            $this->sendWelcomeMessage($context);

            return;
        }

        // Set user to context for use in commands/callbacks
        $context->setUser($user);

        // User exists, continue to next middleware/handler
        $next($context);
    }

    private function sendWelcomeMessage(TelegramContextInterface $context): void
    {
        $context->reply(__('start'), [
            'reply_markup' => $this->keyboardService->getLanguageKeyboard(),
        ]);
    }

    private function getTelegramService()
    {
        return app(\App\Telegram\Services\TelegramBotService::class);
    }
}
