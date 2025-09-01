<?php

namespace App\Telegram\Middleware;

use App\Services\CaptchaService;
use App\Telegram\Contracts\TelegramContextInterface;

class CheckCaptchaMiddleware implements MiddlewareInterface
{
    public function __construct(
        private CaptchaService $captchaService
    ) {}

    public function handle(TelegramContextInterface $context, callable $next): void
    {
        $user = $context->getUserModel();

        if (! $user) {
            $context->sendMessage('❌ User not found');

            return;
        }

        // Check if user needs to solve captcha
        if ($this->captchaService->needsCaptcha($user)) {
            $this->captchaService->sendCaptcha($context, $user);

            return;
        }

        // Captcha not needed or already solved, continue to next middleware
        $next($context);
    }
}
