<?php

namespace App\Telegram\Commands;

use App\Telegram\Core\BaseCommand;
use App\Telegram\Contracts\CommandInterface;
use App\Telegram\Core\TelegramContext;
use App\Application\Services\UserService;
use App\Domain\Repositories\UserRepositoryInterface;

class PendingCommand extends BaseCommand implements CommandInterface
{
    protected string|array $commandName = 'pending';

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
        
        // Get pending count (simplified implementation)
        $pendingCount = $this->getPendingCount($user);
        
        $message = __('pending.queue_status', ['count' => $pendingCount]);
        
        if ($pendingCount > 0) {
            $message .= "\n\n" . __('pending.wait_message');
        } else {
            $message .= "\n\n" . __('pending.no_pending');
        }

        $context->reply($message);
    }

    private function getPendingCount($user): int
    {
        // In a real implementation, you would query the pending queue
        // For now, return a mock value
        return rand(0, 10);
    }
} 
