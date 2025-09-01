<?php

namespace App\Telegram\Middleware;

use App\Domain\Repositories\UserRepositoryInterface;
use App\Telegram\Contracts\TelegramContextInterface;

class CheckBannedUserMiddleware implements MiddlewareInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function handle(TelegramContextInterface $context, callable $next): void
    {
        $message = $context->getMessage();

        if (! $message) {
            $next($context);

            return;
        }

        $chatId = $message['chat']['id'] ?? null;

        if (! $chatId) {
            $next($context);

            return;
        }

        // Check if user is banned
        $user = $this->userRepository->findByTelegramId($chatId);

        if ($user && $user->is_banned) {
            $context->reply(__('errors.user_banned'));

            return;
        }

        // User is not banned, continue to next middleware/handler
        $next($context);
    }
}
