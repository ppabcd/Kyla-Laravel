<?php

namespace App\Telegram\Middleware;

use App\Telegram\Contracts\TelegramContextInterface;
use App\Domain\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Log;

class CheckBannedUserMiddleware implements MiddlewareInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function handle(TelegramContextInterface $context, callable $next): void
    {
        $message = $context->getMessage();
        $chatId = $message->chat->id;

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
