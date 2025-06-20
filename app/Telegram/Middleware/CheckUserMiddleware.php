<?php

namespace App\Telegram\Middleware;

use App\Telegram\Contracts\TelegramContextInterface;
use App\Domain\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Log;

class CheckUserMiddleware implements MiddlewareInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function handle(TelegramContextInterface $context, callable $next): void
    {
        $chatId = null;

        // Get chat ID from message or callback query
        if ($message = $context->getMessage()) {
            $chatId = $message['chat']['id'] ?? null;
        } elseif ($callbackQuery = $context->getCallbackQuery()) {
            $chatId = $callbackQuery['message']['chat']['id'] ?? null;
        }

        if (!$chatId) {
            // No chat ID found, skip this middleware
            $next($context);
            return;
        }

        // Check if user exists
        $user = $this->userRepository->findByTelegramId($chatId);

        if (!$user) {
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
        $keyboard = [
            [
                ['text' => '🇮🇩 Bahasa Indonesia', 'callback_data' => 'lang-id'],
                ['text' => '🇺🇸 English', 'callback_data' => 'lang-en']
            ],
            [
                ['text' => '🇲🇾 Bahasa Melayu', 'callback_data' => 'lang-my'],
                ['text' => '🇮🇳 हिंदी', 'callback_data' => 'lang-in']
            ]
        ];

        $context->reply(__('commands.start'), [
            'reply_markup' => [
                'inline_keyboard' => $keyboard
            ]
        ]);
    }

    private function getTelegramService()
    {
        return app(\App\Telegram\Services\TelegramBotService::class);
    }
}
