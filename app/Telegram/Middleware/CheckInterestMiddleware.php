<?php

namespace App\Telegram\Middleware;

use App\Telegram\Contracts\TelegramContextInterface;
use App\Telegram\Services\KeyboardService;

class CheckInterestMiddleware implements MiddlewareInterface
{
    public function __construct(
        private KeyboardService $keyboardService
    ) {}

    public function handle(TelegramContextInterface $context, callable $next): void
    {
        $user = $context->getUserModel();

        if (! $user) {
            $context->sendMessage('❌ User not found');

            return;
        }

        // Check if user has set interest
        if (! $user->interest) {
            $context->sendMessage(
                __('messages.interest.not_set', [], $user->language_code ?? 'en'),
                ['reply_markup' => $this->keyboardService->getInterestKeyboard($user)]
            );

            return;
        }

        // Interest is set, continue to next middleware
        $next($context);
    }
}
