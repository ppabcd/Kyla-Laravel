<?php

namespace App\Telegram\Commands;

use App\Application\Services\UserService;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Telegram\Contracts\CommandInterface;
use App\Telegram\Core\BaseCommand;
use App\Telegram\Core\TelegramContext;
use Illuminate\Support\Facades\Log;

class TransferCommand extends BaseCommand implements CommandInterface
{
    protected string|array $commandName = 'transfer';

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

        $messageText = $context->getMessage()['text'] ?? '';
        $args = explode(' ', $messageText);

        if (count($args) < 3) {
            $context->reply(__('transfer.usage'));

            return;
        }

        $targetUserId = (int) preg_replace('/\D/', '', $args[1]);
        $amount = (int) preg_replace('/\D/', '', $args[2]);

        if ($amount <= 0) {
            $context->reply(__('transfer.invalid_amount'));

            return;
        }

        $this->processTransfer($context, $telegramUser, $targetUserId, $amount);
    }

    private function processTransfer(TelegramContext $context, array $fromUser, int $targetUserId, int $amount): void
    {
        try {
            $fromUserEntity = $this->userService->findOrCreateUser($fromUser);
            $targetUserEntity = $this->userRepository->findById($targetUserId);

            if (! $targetUserEntity) {
                $context->reply(__('transfer.user_not_found'));

                return;
            }

            // Check if user is admin (owner)
            if ($fromUser['id'] == 1745767543) {
                $this->adminTransfer($context, $targetUserEntity, $amount);

                return;
            }

            $this->userTransfer($context, $fromUserEntity, $targetUserEntity, $amount);

        } catch (\Exception $e) {
            Log::error('Transfer error', [
                'error' => $e->getMessage(),
                'from_user' => $fromUser['id'],
                'target_user' => $targetUserId,
                'amount' => $amount,
            ]);
            $context->reply(__('transfer.failed'));
        }
    }

    private function adminTransfer(TelegramContext $context, $targetUser, int $amount): void
    {
        $currentBalance = $targetUser->balance ?? 0;
        $newBalance = $currentBalance + $amount;

        $this->userService->updateUserProfile($targetUser, ['balance' => $newBalance]);

        $context->reply(__('transfer.admin_success', [
            'amount' => $amount,
            'username' => $targetUser->first_name,
        ]));

        // Notify target user
        $this->notifyUser($targetUser->telegram_id, __('transfer.received_from_admin', ['amount' => $amount]));
    }

    private function userTransfer(TelegramContext $context, $fromUser, $targetUser, int $amount): void
    {
        $fromBalance = $fromUser->balance ?? 0;
        $targetBalance = $targetUser->balance ?? 0;

        if ($fromBalance < $amount) {
            $context->reply(__('transfer.insufficient_balance'));

            return;
        }

        $newFromBalance = $fromBalance - $amount;
        $newTargetBalance = $targetBalance + $amount;

        // Update balances
        $this->userService->updateUserProfile($fromUser, ['balance' => $newFromBalance]);
        $this->userService->updateUserProfile($targetUser, ['balance' => $newTargetBalance]);

        // Notify sender
        $context->reply(__('transfer.success', [
            'amount' => $amount,
            'username' => $targetUser->first_name,
        ]));

        // Notify receiver
        $this->notifyUser($targetUser->telegram_id, __('transfer.received', [
            'amount' => $amount,
            'username' => $fromUser->first_name,
        ]));
    }

    private function notifyUser(int $telegramId, string $message): void
    {
        // In a real implementation, you would send a message to the user
        // For now, we'll just log it
        Log::info('User notification', [
            'telegram_id' => $telegramId,
            'message' => $message,
        ]);
    }
}
