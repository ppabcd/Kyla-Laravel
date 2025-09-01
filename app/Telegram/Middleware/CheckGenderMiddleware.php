<?php

namespace App\Telegram\Middleware;

use App\Telegram\Contracts\TelegramContextInterface;
use App\Telegram\Services\KeyboardService;

class CheckGenderMiddleware implements MiddlewareInterface
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

        // Check if user has set gender
        if (! $user->gender) {
            $context->sendMessage(
                __('messages.gender.not_set', [], $user->language_code ?? 'en'),
                ['reply_markup' => $this->keyboardService->getGenderKeyboard()]
            );

            return;
        }

        // Gender is set, continue to next middleware
        $next($context);
    }
}
