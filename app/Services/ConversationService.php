<?php

namespace App\Services;

use App\Models\User;
use App\Domain\Repositories\PairRepositoryInterface;
use App\Domain\Repositories\PairPendingRepositoryInterface;
use App\Domain\Repositories\ConversationLogRepositoryInterface;
use App\Domain\Repositories\RatingRepositoryInterface;
use App\Telegram\Contracts\TelegramContextInterface;
use App\Telegram\Services\KeyboardService;
use App\Services\CacheService;
use App\Services\PendingService;
use App\Enums\InterestEnum;
use App\Enums\GenderEnum;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ConversationService
{
    public function __construct(
        private PairRepositoryInterface $pairRepository,
        private PairPendingRepositoryInterface $pairPendingRepository,
        private ConversationLogRepositoryInterface $conversationLogRepository,
        private RatingRepositoryInterface $ratingRepository,
        private CacheService $cacheService,
        private KeyboardService $keyboardService,
        private PendingService $pendingService
    ) {
    }

    /**
     * Start a new conversation
     */
    public function startConversation(TelegramContextInterface $context): bool
    {
        $user = $context->getUserModel();

        if (!$user) {
            $context->sendMessage('❌ User not found');
            return false;
        }

        // Acquire conversation lock
        $lockKey = $this->getConversationLockKey($user->id);
        if (!$this->cacheService->acquireLock($lockKey, 30)) {
            $context->sendMessage(__('messages.conversation.locked', [], $user->language_code ?? 'en'));
            return false;
        }

        try {
            // Check if user already has active pair or pending pair
            if ($this->handleExistingPairOrPendingPair($context, $user)) {
                return false;
            }

            // Attempt to find a match
            return $this->attemptPairing($context, $user);

        } finally {
            $this->cacheService->releaseLock($lockKey);
        }
    }

    /**
     * Stop current conversation
     */
    public function stopConversation(TelegramContextInterface $context, ?callable $callback = null): bool
    {
        $user = $context->getUserModel();

        if (!$user) {
            $context->sendMessage('❌ User not found');
            return false;
        }

        $lockKey = $this->getConversationStopLockKey($user->id);
        if (!$this->cacheService->acquireLock($lockKey, 30)) {
            $context->sendMessage(__('messages.conversation.locked', [], $user->language_code ?? 'en'));
            return false;
        }

        try {
            // Check for pending pair first
            $pendingPairs = $this->pairPendingRepository->findByUserId($user->id);
            if (!empty($pendingPairs)) {
                $this->pairPendingRepository->deleteByUserId($user->id);
                $context->sendMessage(
                    __('messages.pending_pair.deleted', [], $user->language_code ?? 'en'),
                    ['reply_markup' => $this->keyboardService->getSearchKeyboard()]
                );

                if ($callback) {
                    return $callback();
                }
                return true;
            }

            // Check for active pair
            $activePair = $this->pairRepository->findActivePairByUserId($user->id);
            if ($activePair) {
                $partner = $activePair->getPartner($user);

                // End the pair
                $this->pairRepository->endPair($activePair->id, 'user_stop');

                // Send messages to both users
                $context->sendMessage(
                    __('messages.pair.deleted', [], $user->language_code ?? 'en'),
                    ['reply_markup' => $this->keyboardService->getSearchWithReportKeyboard()]
                );

                if ($partner) {
                    // Send message to partner via Telegram API
                    $this->sendMessageToUser(
                        $partner->telegram_id,
                        __('messages.pair.deleted', [], $partner->language_code ?? 'en'),
                        ['reply_markup' => $this->keyboardService->getSearchWithReportKeyboard()]
                    );
                }

                // Clean cache
                $this->removeCache($user, $partner);

                // Log conversation
                $this->createConversationLog([
                    'userId' => $user->id,
                    'chatId' => $context->getChatId(),
                    'messageId' => null,
                    'convId' => $activePair->id,
                    'timestamp' => $activePair->created_at->timestamp
                ]);

                if ($partner) {
                    $this->createConversationLog([
                        'userId' => $partner->id,
                        'chatId' => $partner->telegram_id,
                        'messageId' => null,
                        'convId' => $activePair->id,
                        'timestamp' => $activePair->created_at->timestamp
                    ]);
                }

                if ($callback) {
                    return $callback();
                }
                return true;
            }

            // No active conversation
            if (!$callback) {
                $context->sendMessage(
                    __('messages.conversation.not_exists', [], $user->language_code ?? 'en'),
                    ['reply_markup' => $this->keyboardService->getSearchKeyboard()]
                );
            }

            if ($callback) {
                return $callback();
            }
            return true;

        } finally {
            $this->cacheService->releaseLock($lockKey);
        }
    }

    /**
     * End current conversation and start new one
     */
    public function nextConversation(TelegramContextInterface $context): bool
    {
        return $this->stopConversation($context, function () use ($context) {
            return $this->startConversation($context);
        });
    }

    /**
     * Handle existing pair or pending pair
     */
    private function handleExistingPairOrPendingPair(TelegramContextInterface $context, User $user): bool
    {
        // Check for active pair
        $activePair = $this->pairRepository->findActivePairByUserId($user->id);
        if ($activePair) {
            $context->sendMessage(
                __('messages.pair.exists', [], $user->language_code ?? 'en'),
                ['reply_markup' => $this->keyboardService->getConversationKeyboard()]
            );
            return true;
        }

        // Check for pending pair
        $pendingPairs = $this->pairPendingRepository->findByUserId($user->id);
        if (!empty($pendingPairs)) {
            $context->sendMessage(
                __('messages.pending_pair.exists', [], $user->language_code ?? 'en'),
                ['reply_markup' => $this->keyboardService->getSearchingKeyboard()]
            );
            return true;
        }

        return false;
    }

    /**
     * Attempt to pair users
     */
    private function attemptPairing(TelegramContextInterface $context, User $user): bool
    {
        // Find available match based on user's interest and gender
        $availableMatch = $this->findAvailableMatch($user);

        if ($availableMatch) {
            return $this->processPairUserWithPartner($context, $user, $availableMatch);
        }

        // No match found, add to pending queue
        $this->pairPendingRepository->create([
            'user_id' => $user->id,
            'gender' => $user->gender,
            'interest' => $user->interest,
            'emoji' => $user->gender_icon,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $context->sendMessage(
            __('messages.pending_pair.created', [], $user->language_code ?? 'en'),
            ['reply_markup' => $this->keyboardService->getSearchingKeyboard()]
        );

        return true;
    }

    /**
     * Find available match for user
     */
    private function findAvailableMatch(User $user): ?object
    {
        $targetGender = $user->interest;
        $userGender = $user->gender;

        // Find users looking for this user's gender
        return $this->pairPendingRepository->findAvailableMatch($userGender, $targetGender);
    }

    /**
     * Process pairing between two users
     */
    private function processPairUserWithPartner(TelegramContextInterface $context, User $user, object $availableMatch): bool
    {
        try {
            DB::beginTransaction();

            // Create pair
            $pair = $this->pairRepository->create([
                'first_user_id' => $user->id,
                'second_user_id' => $availableMatch->user_id,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Remove from pending queue
            $this->pairPendingRepository->deleteByUserId($user->id);
            $this->pairPendingRepository->deleteByUserId($availableMatch->user_id);

            // Get partner user model
            $partner = User::find($availableMatch->user_id);

            // Get ratings for both users
            $partnerRating = $this->ratingRepository->getAverageRatingByUserId($partner->id);
            $userRating = $this->ratingRepository->getAverageRatingByUserId($user->id);

            // Send messages to both users
            $context->sendMessage(
                __('messages.pair.created', [
                    'genderIcon' => $availableMatch->emoji ?? '👤',
                    'rating' => $this->getStarRating($partnerRating['average'] ?? 0),
                    'totalRating' => $partnerRating['total'] ?? 0
                ], $user->language_code ?? 'en'),
                ['reply_markup' => $this->keyboardService->getConversationKeyboard()]
            );

            $this->sendMessageToUser(
                $partner->telegram_id,
                __('messages.pair.created', [
                    'genderIcon' => $user->gender_icon ?? '👤',
                    'rating' => $this->getStarRating($userRating['average'] ?? 0),
                    'totalRating' => $userRating['total'] ?? 0
                ], $partner->language_code ?? 'en'),
                ['reply_markup' => $this->keyboardService->getConversationKeyboard()]
            );

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create pair', [
                'user_id' => $user->id,
                'partner_id' => $availableMatch->user_id,
                'error' => $e->getMessage()
            ]);

            $context->sendMessage('❌ Failed to create match. Please try again.');
            return false;
        }
    }

    /**
     * Get conversation lock key
     */
    private function getConversationLockKey(int $userId): string
    {
        return "conversation_lock:{$userId}";
    }

    /**
     * Get conversation stop lock key
     */
    private function getConversationStopLockKey(int $userId): string
    {
        return "conversation_stop_lock:{$userId}";
    }

    /**
     * Create conversation log
     */
    private function createConversationLog(array $data): void
    {
        $conversationId = null;
        if (isset($data['convId']) && isset($data['timestamp'])) {
            $conversationId = $data['timestamp'] . '_' . $data['convId'];
        }

        $this->conversationLogRepository->create([
            'user_id' => $data['userId'],
            'chat_id' => $data['chatId'],
            'message_id' => $data['messageId'],
            'conv_id' => $conversationId,
            'is_action' => 0,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Remove cache for users
     */
    private function removeCache(User $user, ?User $partner = null): void
    {
        $this->cacheService->delete("enable-media:{$user->id}");
        $this->cacheService->delete("pair:{$user->id}");

        if ($partner) {
            $this->cacheService->delete("enable-media:{$partner->id}");
            $this->cacheService->delete("pair:{$partner->id}");
        }
    }

    /**
     * Send message to user via Telegram API
     */
    private function sendMessageToUser(string $telegramId, string $message, array $options = []): void
    {
        // This should be implemented based on your Telegram bot client
        // For now, just log it
        Log::info('Sending message to user', [
            'telegram_id' => $telegramId,
            'message' => $message,
            'options' => $options
        ]);
    }

    /**
     * Get star rating string
     */
    private function getStarRating(float $rating): string
    {
        $fullStars = floor($rating);
        $halfStar = ($rating - $fullStars) >= 0.5 ? 1 : 0;
        $emptyStars = 5 - $fullStars - $halfStar;

        return str_repeat('⭐', (int) $fullStars) .
            str_repeat('💫', $halfStar) .
            str_repeat('⚪', (int) $emptyStars);
    }
}
