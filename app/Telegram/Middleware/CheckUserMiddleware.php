<?php

namespace App\Telegram\Middleware;

use App\Telegram\Contracts\TelegramContextInterface;
use App\Domain\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Log;

class CheckUserMiddleware implements MiddlewareInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function handle(TelegramContextInterface $context, callable $next): void
    {
        $message = $context->getMessage();
        $chatId = $message->chat->id;

        // Check if user exists
        $user = $this->userRepository->findByTelegramId($chatId);
        
        if (!$user) {
            // User doesn't exist, send welcome message
            $this->sendWelcomeMessage($context);
            return;
        }

        // User exists, continue to next middleware/handler
        $next($context);
    }

    private function sendWelcomeMessage(TelegramContextInterface $context): void
    {
        $keyboard = [
            [
                ['text' => '🇮🇩 Bahasa Indonesia', 'callback_data' => 'language:id'],
                ['text' => '🇺🇸 English', 'callback_data' => 'language:en']
            ],
            [
                ['text' => '🇲🇾 Bahasa Melayu', 'callback_data' => 'language:ms'],
                ['text' => '🇮🇳 हिंदी', 'callback_data' => 'language:in']
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
