<?php

namespace App\Telegram\Commands;

use App\Telegram\Core\BaseCommand;
use App\Telegram\Contracts\CommandInterface;
use App\Telegram\Core\TelegramContext;
use App\Application\Services\UserService;
use App\Domain\Repositories\UserRepositoryInterface;

class InvalidateSessionCommand extends BaseCommand implements CommandInterface
{
    protected string|array $commandName = 'invalidate';

    public function __construct(
        private UserService $userService,
        private UserRepositoryInterface $userRepository
    ) {}

    public function handle(\App\Telegram\Contracts\TelegramContextInterface $context): void
    {
        $telegramUser = $context->getFrom();
        if (!$telegramUser) {
            $context->reply('❌ Unable to identify user');
            return;
        }

        $user = $this->userService->findOrCreateUser($telegramUser);
        
        // Reset user session data
        $this->resetUserSession($user);
        
        $context->reply(__('session.invalidated'));
    }

    private function resetUserSession($user): void
    {
        // Reset session-related fields
        $this->userService->updateUserProfile($user, [
            'bot_mode' => 'anonymous',
            'session_data' => null,
            'captcha_code' => null,
            'captcha_expired_at' => null
        ]);
    }
} 
