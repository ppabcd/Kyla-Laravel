<?php

namespace App\Telegram\Callbacks;

use App\Telegram\Core\BaseCallback;
use App\Telegram\Contracts\CallbackInterface;
use App\Telegram\Core\TelegramContext;
use App\Application\Services\UserService;
use App\Domain\Repositories\UserRepositoryInterface;

class PrivacyCallback extends BaseCallback implements CallbackInterface
{
    protected string|array $callbackName = ['privacy'];

    public function __construct(
        private UserService $userService,
        private UserRepositoryInterface $userRepository
    ) {}

    public function handle(\App\Telegram\Contracts\TelegramContextInterface $context): void

    {
        $telegramUser = $context->getUser();
        if (!$telegramUser) {
            $context->reply('❌ Unable to identify user');
            return;
        }
        $lang = $telegramUser['language_code'] ?? 'en';
        $message = __('privacy.policy', [], $lang);
        $context->reply($message, ['parse_mode' => 'Markdown']);
    }
} 
