<?php

namespace App\Telegram\Callbacks;

use App\Infrastructure\Repositories\PairPendingRepository;
use App\Telegram\Contracts\CallbackInterface;
use App\Telegram\Contracts\TelegramContextInterface;
use App\Telegram\Core\BaseCallback;

class QueueStatusCallback extends BaseCallback implements CallbackInterface
{
    protected string|array $callbackName = 'queue_status';

    public function __construct(
        private PairPendingRepository $pairPendingRepository
    ) {}

    public function handle(TelegramContextInterface $context): void
    {
        $user = $context->getUser();

        if (! $user) {
            $context->answerCallbackQuery('User not found');

            return;
        }

        $totalPending = $this->pairPendingRepository->countPendingPairs();
        $userPosition = $this->getUserPosition($user->id);

        $message = "📊 Queue Status:\n\n";
        $message .= "👥 Total users in queue: {$totalPending}\n";
        if ($userPosition > 0) {
            $message .= "📍 Your position: #{$userPosition}\n";
        } else {
            $message .= "❌ You are not currently in the queue\n";
        }

        $context->sendMessage($message);
        $context->answerCallbackQuery();
    }

    private function getUserPosition(int $userId): int
    {
        $userPending = $this->pairPendingRepository->findByUserId($userId);
        if (! $userPending) {
            return 0;
        }

        return $this->pairPendingRepository->countPendingPairs() -
               $this->pairPendingRepository->findPendingPairs()->search(function ($item) use ($userId) {
                   return $item->user_id === $userId;
               });
    }
}
