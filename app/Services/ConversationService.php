<?php

namespace App\Services;

use App\Domain\Repositories\ConversationLogRepositoryInterface;
use App\Domain\Repositories\PairPendingRepositoryInterface;
use App\Domain\Repositories\PairRepositoryInterface;
use App\Domain\Repositories\RatingRepositoryInterface;
use App\Models\User;
use App\Telegram\Contracts\TelegramContextInterface;
use App\Telegram\Core\TelegramContext;
use App\Telegram\Services\KeyboardService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ConversationService
{
    public function __construct(
        private PairRepositoryInterface $pairRepository,
        private PairPendingRepositoryInterface $pairPendingRepository,
        private ConversationLogRepositoryInterface $conversationLogRepository,
        private RatingRepositoryInterface $ratingRepository,
        private KeyboardService $keyboardService,
        private PendingService $pendingService
    ) {}

    /**
     * Start a new conversation
     */
    public function startConversation(TelegramContextInterface $context): bool
    {
        $user = $context->getUserModel();

        if (! $user) {
            $context->sendMessage('❌ User not found');

            return false;
        }

        // Acquire conversation lock using Laravel Cache lock
        $lockKey = $this->getConversationLockKey($user->id);
        $lock = Cache::lock($lockKey, 30);

        if (! $lock->get()) {
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
            $lock->release();
        }
    }

    /**
     * Stop current conversation
     */
    public function stopConversation(TelegramContextInterface $context, ?callable $callback = null): bool
    {
        $user = $context->getUserModel();

        if (! $user) {
            $context->sendMessage('❌ User not found');

            return false;
        }

        $lockKey = $this->getConversationStopLockKey($user->id);
        $lock = Cache::lock($lockKey, 30);

        if (! $lock->get()) {
            $context->sendMessage(__('messages.conversation.locked', [], $user->language_code ?? 'en'));

            return false;
        }

        try {
            // Check for pending pair first
            $pendingPairs = $this->pairPendingRepository->findByUserId($user->id);
            if (! empty($pendingPairs)) {
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
                $partner = $activePair->getOtherUser($user->id);

                // End the pair
                $this->pairRepository->endPair($activePair, $user->id, 'user_stop');

                // Send messages to both users
                $context->sendMessage(
                    __('messages.pair.deleted', [], $user->language_code ?? 'en'),
                    ['reply_markup' => $this->keyboardService->getSearchWithReportKeyboard()]
                );

                if ($partner) {
                    // Send message to partner using their language preference
                    TelegramContext::sendMessageToChat(
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
                    'messageId' => time() + $user->id,
                    'convId' => $activePair->id,
                    'timestamp' => $activePair->created_at->timestamp,
                ]);

                if ($partner) {
                    $this->createConversationLog([
                        'userId' => $partner->id,
                        'chatId' => $partner->telegram_id,
                        'messageId' => time() + $partner->id,
                        'convId' => $activePair->id,
                        'timestamp' => $activePair->created_at->timestamp,
                    ]);
                }

                if ($callback) {
                    return $callback();
                }

                return true;
            }

            // No active conversation
            if (! $callback) {
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
            $lock->release();
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
        if (! empty($pendingPairs)) {
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
            'emoji' => $this->resolveGenderIcon($user),
            'language' => $user->language_code ?? 'en',
            'platform_id' => $user->platform_user_id ?? 0,
            'is_premium' => $user->is_premium ?? false,
            'is_safe_mode' => $user->is_safe_mode ?? false,
        ]);

        // Check if queue is overcrowded and offer random gender option
        $isOvercrowded = $this->pairPendingRepository->isQueueOvercrowded();
        $isGenderBalanced = $this->pairPendingRepository->isGenderBalanced();

        if ($isOvercrowded && ! $isGenderBalanced) {
            $pendingCount = $this->pairPendingRepository->countPendingPairs();
            $message = __('messages.queue.overcrowded_message', ['count' => $pendingCount], $user->language_code ?? 'en');
            $keyboard = $this->keyboardService->getQueueOvercrowdedKeyboard();

            $context->sendMessage($message, array_merge($keyboard, ['parse_mode' => 'Markdown']));
        } else {
            $context->sendMessage(
                __('messages.pending_pair.created', [], $user->language_code ?? 'en'),
                ['reply_markup' => $this->keyboardService->getSearchingKeyboard()]
            );
        }

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

            // Create pair (always put smaller ID in user_id, larger ID in partner_id)
            $userId = min($user->id, $availableMatch->user_id);
            $partnerId = max($user->id, $availableMatch->user_id);

            $pair = $this->pairRepository->create([
                'user_id' => $userId,
                'partner_id' => $partnerId,
                'status' => 'active',
                'active' => true,
                'started_at' => now(),
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
                    'genderIcon' => $this->resolveGenderIcon($partner),
                    'rating' => $this->getStarRating($partnerRating['average'] ?? 0),
                    'totalRating' => $partnerRating['total'] ?? 0,
                ], $user->language_code ?? 'en'),
                ['reply_markup' => $this->keyboardService->getConversationKeyboard()]
            );

            // Send message to partner using their language preference
            TelegramContext::sendMessageToChat(
                $partner->telegram_id,
                __('messages.pair.created', [
                    'genderIcon' => $this->resolveGenderIcon($user),
                    'rating' => $this->getStarRating($userRating['average'] ?? 0),
                    'totalRating' => $userRating['total'] ?? 0,
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
                'error' => $e->getMessage(),
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
            $conversationId = $data['timestamp'].'_'.$data['convId'];
        }

        $this->conversationLogRepository->create([
            'user_id' => $data['userId'],
            'chat_id' => $data['chatId'],
            'message_id' => $data['messageId'],
            'conv_id' => $conversationId,
            'is_action' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Remove cache for users
     */
    private function removeCache(User $user, ?User $partner = null): void
    {
        Cache::forget("enable-media:{$user->id}");
        Cache::forget("pair:{$user->id}");

        if ($partner) {
            Cache::forget("enable-media:{$partner->id}");
            Cache::forget("pair:{$partner->id}");
        }
    }

    /**
     * Get star rating string
     */
    private function getStarRating(float $rating): string
    {
        $fullStars = floor($rating);
        $halfStar = ($rating - $fullStars) >= 0.5 ? 1 : 0;
        $emptyStars = 5 - $fullStars - $halfStar;

        return str_repeat('⭐', (int) $fullStars).
            str_repeat('💫', $halfStar).
            str_repeat('⚪', (int) $emptyStars);
    }

    private function resolveGenderIcon(User $user): string
    {
        if (! empty($user->gender_icon)) {
            return $user->gender_icon;
        }

        return match ($user->gender) {
            'male' => '👨',
            'female' => '👩',
            default => '👤',
        };
    }
}
