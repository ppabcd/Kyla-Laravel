<?php

namespace App\Telegram\Callbacks;

use App\Telegram\Core\BaseCallback;
use App\Telegram\Contracts\CallbackInterface;
use App\Telegram\Core\TelegramContext;
use App\Application\Services\UserService;
use App\Domain\Repositories\UserRepositoryInterface;

class PendingCallback extends BaseCallback implements CallbackInterface
{
    protected string|array $callbackName = ['pending'];

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
        $user = $this->userService->findOrCreateUser($telegramUser);
        $pendingCount = rand(0, 10); // Simulasi jumlah pending
        $message = __('pending.queue_status', ['count' => $pendingCount]);
        if ($pendingCount > 0) {
            $message .= "\n\n" . __('pending.wait_message');
        } else {
            $message .= "\n\n" . __('pending.no_pending');
        }
        $context->reply($message);
    }
} 
