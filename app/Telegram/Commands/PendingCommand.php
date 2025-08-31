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
            $message = __('messages.queue.overcrowded_message', ['count' => $pendingCount]);
            $keyboard = $this->keyboardService->getQueueOvercrowdedKeyboard();

            $context->reply($message, $keyboard);
        } else {
            $message = "📊 **Queue Status**\n\n";
            $message .= "👥 **Total users in queue:** {$pendingCount}\n";

            $userPending = $this->pairPendingRepository->findByUserId($user->id);
            if ($userPending) {
                $userPosition = $this->getUserPosition($user->id);
                $message .= "📍 **Your position:** #{$userPosition}\n";
                $estimatedWait = $this->calculateEstimatedWaitTime($userPosition);
                $message .= "⏱️ **Estimated wait:** ~{$estimatedWait} minutes\n\n";
                $message .= '⏳ Please wait while we find you a match...';
            } else {
                $message .= "❌ **Status:** You are not currently in the queue\n\n";
                $message .= '💡 **Tip:** Use /search to join the queue!';
            }

            // Gender distribution info
            $message .= "\n\n⚖️ **Gender Distribution:**\n";
            $message .= "👦 Males: {$genderBalance['male_count']}\n";
            $message .= "👧 Females: {$genderBalance['female_count']}\n";

            if ($genderBalance['is_balanced']) {
                $message .= '✅ **Balance:** Good';
            } else {
                $underrepresented = $this->pairPendingRepository->getUnderrepresentedGender();
                $genderName = $underrepresented === 1 ? 'males' : 'females';
                $message .= "⚠️ **Balance:** Need more {$genderName}";
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

    private function calculateEstimatedWaitTime(int $position): int
    {
        // Base calculation: assume 1 match every 2-3 minutes on average
        $averageMatchTime = 2.5;

        // Factor in position (users ahead in queue)
        $baseWait = ($position - 1) * $averageMatchTime;

        // Add some buffer for matching complexity
        $buffer = min(5, $position * 0.5);

        return max(1, (int) ceil($baseWait + $buffer));
    }
}
