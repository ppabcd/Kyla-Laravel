<?php

namespace App\Telegram\Commands;

use App\Application\Services\UserService;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Infrastructure\Repositories\PairPendingRepository;
use App\Telegram\Contracts\CommandInterface;
use App\Telegram\Core\BaseCommand;
use App\Telegram\Services\KeyboardService;

class PendingCommand extends BaseCommand implements CommandInterface
{
    protected string|array $commandName = 'pending';

    public function __construct(
        private UserService $userService,
        private UserRepositoryInterface $userRepository,
        private PairPendingRepository $pairPendingRepository,
        private KeyboardService $keyboardService
    ) {}

    public function handle(\App\Telegram\Contracts\TelegramContextInterface $context): void
    {
        $telegramUser = $context->getUser();
        if (! $telegramUser) {
            $context->reply(__('❌ Unable to identify user'));

            return;
        }

        $user = $this->userService->findOrCreateUser($telegramUser);

        $pendingCount = $this->pairPendingRepository->countPendingPairs();
        $isOvercrowded = $this->pairPendingRepository->isQueueOvercrowded();
        $genderBalance = $this->pairPendingRepository->getGenderBalance();

        if ($isOvercrowded && ! $genderBalance['is_balanced']) {
            $message = __('queue.overcrowded_message', ['count' => $pendingCount]);
            $keyboard = $this->keyboardService->getQueueOvercrowdedKeyboard();

            $context->reply($message, $keyboard);
        } else {
            $message = "📊 Queue Status:\n\n";
            $message .= "👥 Total users in queue: {$pendingCount}\n";

            $userPending = $this->pairPendingRepository->findByUserId($user->id);
            if ($userPending) {
                $userPosition = $this->getUserPosition($user->id);
                $message .= "📍 Your position: #{$userPosition}\n";
                $message .= "\n⏳ Please wait while we find you a match...";
            } else {
                $message .= "❌ You are not currently in the queue\n";
                $message .= "\n💡 Use /search to join the queue!";
            }

            $context->reply($message);
        }
    }

    private function getUserPosition(int $userId): int
    {
        $userPending = $this->pairPendingRepository->findByUserId($userId);
        if (! $userPending) {
            return 0;
        }

        return $this->pairPendingRepository->findPendingPairs()
            ->search(function ($item) use ($userId) {
                return $item->user_id === $userId;
            }) + 1;
    }
}
