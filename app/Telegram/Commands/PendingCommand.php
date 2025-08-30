<?php

namespace App\Telegram\Commands;

use App\Application\Services\UserService;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Telegram\Contracts\CommandInterface;
use App\Telegram\Core\BaseCommand;

class PendingCommand extends BaseCommand implements CommandInterface
{
    protected string|array $commandName = 'pending';

    public function __construct(
        private UserService $userService,
        private UserRepositoryInterface $userRepository
    ) {}

    public function handle(\App\Telegram\Contracts\TelegramContextInterface $context): void
    {
        $telegramUser = $context->getUser();
        if (! $telegramUser) {
            $context->reply('❌ Unable to identify user');

            return;
        }

        $user = $this->userService->findOrCreateUser($telegramUser);

        // Get pending count (simplified implementation)
        $pendingCount = $this->getPendingCount($user);

        $message = __('pending.queue_status', ['count' => $pendingCount]);

        if ($pendingCount > 0) {
            $message .= "\n\n".__('pending.wait_message');
        } else {
            $message .= "\n\n".__('pending.no_pending');
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
