<?php

namespace App\Telegram\Callbacks;

use App\Infrastructure\Repositories\PairPendingRepository;
use App\Telegram\Contracts\CallbackInterface;
use App\Telegram\Contracts\TelegramContextInterface;
use App\Telegram\Core\BaseCallback;
use App\Telegram\Services\KeyboardService;

class QueueStatusCallback extends BaseCallback implements CallbackInterface
{
    protected string|array $callbackName = 'queue_status';

    public function __construct(
        private PairPendingRepository $pairPendingRepository,
        private KeyboardService $keyboardService
    ) {}

    public function handle(TelegramContextInterface $context): void
    {
        $user = $context->getUser();

        if (! $user) {
            $context->answerCallbackQuery(__('User not found'));

            return;
        }

        $totalPending = $this->pairPendingRepository->countPendingPairs();
        $userPosition = $this->getUserPosition($user->id);
        $isOvercrowded = $this->pairPendingRepository->isQueueOvercrowded();
        $genderBalance = $this->pairPendingRepository->getGenderBalance();

        if ($isOvercrowded && ! $genderBalance['is_balanced']) {
            $message = __('queue.overcrowded_message', ['count' => $totalPending]);
            $keyboard = $this->keyboardService->getQueueOvercrowdedKeyboard();

            $context->sendMessage($message, $keyboard);
        } else {
            $message = "📊 **Queue Status**\n\n";
            $message .= "👥 **Total users in queue:** {$totalPending}\n";

            if ($userPosition > 0) {
                $message .= "📍 **Your position:** #{$userPosition}\n";
                $estimatedWait = $this->calculateEstimatedWaitTime($userPosition);
                $message .= "⏱️ **Estimated wait:** ~{$estimatedWait} minutes\n\n";
            } else {
                $message .= "❌ **Status:** You are not currently in the queue\n";
                $message .= "💡 **Tip:** Use /search to join the queue!\n\n";
            }

            // Gender distribution info
            $message .= "⚖️ **Gender Distribution:**\n";
            $message .= "👦 Males: {$genderBalance['male_count']}\n";
            $message .= "👧 Females: {$genderBalance['female_count']}\n";

            if ($genderBalance['is_balanced']) {
                $message .= "✅ **Balance:** Good\n";
            } else {
                $underrepresented = $this->pairPendingRepository->getUnderrepresentedGender();
                $genderName = $underrepresented === 1 ? 'males' : 'females';
                $message .= "⚠️ **Balance:** Need more {$genderName}\n";
            }

            $context->sendMessage($message);
        }

        $context->answerCallbackQuery();
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
