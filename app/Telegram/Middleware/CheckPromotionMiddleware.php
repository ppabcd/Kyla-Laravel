<?php

namespace App\Telegram\Middleware;

use App\Telegram\Contracts\TelegramContextInterface;
use App\Services\CacheService;

class CheckPromotionMiddleware implements MiddlewareInterface
{
    public function __construct(
        private CacheService $cacheService
    ) {
    }

    public function handle(TelegramContextInterface $context, callable $next): void
    {
        $user = $context->getUserModel();

        if (!$user) {
            $context->sendMessage('❌ User not found');
            return;
        }

        // Check if user has seen promotion message
        $promotionKey = "promotion_shown:{$user->id}";
        if (!$this->cacheService->has($promotionKey)) {
            // Show promotion message
            $this->sendPromotionMessage($context, $user);

            // Mark promotion as shown (cache for 24 hours)
            $this->cacheService->put($promotionKey, true, 86400);
            return;
        }

        // Promotion already shown, continue to next middleware
        $next($context);
    }

    private function sendPromotionMessage(TelegramContextInterface $context, $user): void
    {
        $message = __('messages.promotion.message', [], $user->language_code ?? 'en');
        $context->sendMessage($message);
    }
}
