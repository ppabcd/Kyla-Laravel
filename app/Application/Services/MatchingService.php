<?php

namespace App\Application\Services;

use App\Domain\Repositories\PairRepositoryInterface;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Models\Pair;
use App\Models\User;
use App\Telegram\Services\TelegramBotService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Matching Service
 *
 * Application service responsible for user matching logic
 * Following Single Responsibility Principle and Clean Architecture
 */
class MatchingService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PairRepositoryInterface $pairRepository,
        private UserService $userService,
        private TelegramBotService $telegramService
    ) {}

    /**
     * Find a suitable match for a user
     */
    public function findMatch(User $user): ?User
    {
        if (! $user->canMatch()) {
            Log::warning('User cannot match', ['user_id' => $user->id]);

            return null;
        }

        // Check if user already has an active pair
        $activePair = $this->pairRepository->findActivePairByUserId($user->id);
        if ($activePair) {
            Log::info('User already has active pair', [
                'user_id' => $user->id,
                'pair_id' => $activePair->id,
            ]);

            return null;
        }

        // Find potential matches
        $potentialMatches = $this->findPotentialMatches($user);

        if ($potentialMatches->isEmpty()) {
            Log::info('No potential matches found', ['user_id' => $user->id]);

            return null;
        }

        // Score and rank matches
        $rankedMatches = $this->rankMatches($user, $potentialMatches);

        // Return the best match
        $bestMatch = $rankedMatches->first();

        Log::info('Match found', [
            'user_id' => $user->id,
            'match_id' => $bestMatch->id,
            'score' => $bestMatch->match_score ?? 0,
        ]);

        return $bestMatch;
    }

    /**
     * Create a pair between two users
     */
    public function createPair(User $user, User $partner): ?Pair
    {
        // Validate both users can match
        if (! $user->canMatch() || ! $partner->canMatch()) {
            Log::warning('One or both users cannot match', [
                'user_id' => $user->id,
                'partner_id' => $partner->id,
            ]);

            return null;
        }

        // Check if either user already has an active pair
        if (
            $this->pairRepository->findActivePairByUserId($user->id) ||
            $this->pairRepository->findActivePairByUserId($partner->id)
        ) {
            Log::warning('One or both users already have active pairs', [
                'user_id' => $user->id,
                'partner_id' => $partner->id,
            ]);

            return null;
        }

        // Create the pair
        $pair = $this->pairRepository->createPair($user, $partner);
        $this->pairRepository->startPair($pair);

        // User statistics tracking removed - column doesn't exist in database

        Log::info('Pair created', [
            'pair_id' => $pair->id,
            'user_id' => $user->id,
            'partner_id' => $partner->id,
        ]);

        return $pair;
    }

    /**
     * End a pair
     */
    public function endPair(Pair $pair, int $endedBy, ?string $reason = null): bool
    {
        if (! $pair->isActive()) {
            Log::warning('Attempted to end non-active pair', ['pair_id' => $pair->id]);

            return false;
        }

        $success = $this->pairRepository->endPair($pair, $endedBy, $reason);

        if ($success) {
            Log::info('Pair ended', [
                'pair_id' => $pair->id,
                'ended_by' => $endedBy,
                'reason' => $reason,
                'duration_minutes' => $pair->getDuration(),
            ]);

            // Clear match caches
            $this->clearMatchCaches($pair->user_id);
            $this->clearMatchCaches($pair->partner_id);
        }

        return $success;
    }

    /**
     * Get conversation partner for a user
     */
    public function getConversationPartner(User $user): ?User
    {
        $activePair = $this->pairRepository->findActivePairByUserId($user->id);

        if (! $activePair) {
            return null;
        }

        return $activePair->getOtherUser($user->id);
    }

    /**
     * Get conversation ID for a pair
     */
    public function getConversationId(User $user, User $partner): ?string
    {
        $activePair = $this->pairRepository->findActivePairByUserId($user->id);

        if (! $activePair || ! $activePair->hasUser($partner->id)) {
            return null;
        }

        return $activePair->id.'_'.$activePair->created_at->timestamp;
    }

    /**
     * End conversation for a user
     */
    public function endConversation(User $user): bool
    {
        $activePair = $this->pairRepository->findActivePairByUserId($user->id);

        if (! $activePair) {
            return false;
        }

        return $this->endPair($activePair, $user->id, 'user_ended');
    }

    /**
     * Get matching statistics
     */
    public function getMatchingStatistics(): array
    {
        return Cache::remember('matching_statistics', 3600, function () {
            $stats = $this->pairRepository->getPairStatistics();

            return [
                'total_pairs' => $stats['total_pairs'],
                'active_pairs' => $stats['active_pairs'],
                'ended_pairs' => $stats['ended_pairs'],
                'average_duration_minutes' => $stats['average_duration_minutes'],
                'success_rate' => $stats['total_pairs'] > 0
                    ? ($stats['pairs_with_ratings'] / $stats['total_pairs']) * 100
                    : 0,
                'average_rating' => $stats['average_rating'],
                'active_users_available' => $this->userRepository->findUsersForMatching('', '', 0, 1000)->count(),
            ];
        });
    }

    /**
     * Get match statistics (alias for test compatibility)
     */
    public function getMatchStats(): array
    {
        $stats = $this->pairRepository->getPairStatistics();
        $userStats = $this->userService->getUserStatistics();

        return [
            'active_pairs' => $stats['active_pairs'],
            'total_users' => $userStats['total_users'],
            'premium_users' => $userStats['premium_users'],
            'average_duration' => $stats['average_duration_minutes'],
            'success_rate' => $stats['total_pairs'] > 0
                ? ($stats['pairs_with_ratings'] / $stats['total_pairs']) * 100
                : 0,
            'match_rate' => $userStats['total_users'] > 0
                ? ($stats['active_pairs'] / $userStats['total_users']) * 100
                : 0,
        ];
    }

    /**
     * Find users who are ready for matching
     */
    public function findUsersReadyForMatching(int $limit = 100): Collection
    {
        return Cache::remember("users_ready_for_matching:{$limit}", 300, function () use ($limit) {
            return $this->userRepository->searchUsers([
                'visibility' => true,
                'is_banned' => false,
            ], 1, $limit)->getCollection()
                ->filter(function ($user) {
                    return $user->canMatch() &&
                        ! $this->pairRepository->findActivePairByUserId($user->id);
                });
        });
    }

    /**
     * Auto-match users based on compatibility
     */
    public function performAutoMatching(int $maxPairs = 50): int
    {
        $availableUsers = $this->findUsersReadyForMatching(500);
        $pairsCreated = 0;
        $processed = collect();

        foreach ($availableUsers as $user) {
            if ($processed->contains($user->id) || $pairsCreated >= $maxPairs) {
                continue;
            }

            $match = $this->findMatch($user);

            if ($match && ! $processed->contains($match->id)) {
                $pair = $this->createPair($user, $match);

                if ($pair) {
                    $pairsCreated++;
                    $processed->push($user->id);
                    $processed->push($match->id);
                }
            }
        }

        Log::info('Auto-matching completed', [
            'pairs_created' => $pairsCreated,
            'users_processed' => $processed->count(),
        ]);

        return $pairsCreated;
    }

    /**
     * Cleanup expired or inactive pairs
     */
    public function cleanupInactivePairs(): int
    {
        $inactivePairs = $this->pairRepository->findInactivePairs(60); // 1 hour inactive
        $expiredPairs = $this->pairRepository->findExpiredPairs(240); // 4 hours max duration

        $allPairsToEnd = $inactivePairs->merge($expiredPairs)->unique('id');
        $endedCount = 0;

        foreach ($allPairsToEnd as $pair) {
            if ($this->endPair($pair, 0, 'Auto-ended due to inactivity')) {
                $endedCount++;
            }
        }

        Log::info('Inactive pairs cleanup completed', ['pairs_ended' => $endedCount]);

        return $endedCount;
    }

    /**
     * Private helper methods
     */
    private function findPotentialMatches(User $user): Collection
    {
        $cacheKey = "potential_matches:user:{$user->id}";

        return Cache::remember($cacheKey, 300, function () use ($user) {
            // Check if random matching is enabled
            $randomMatching = config('telegram.matching.random_matching', false);

            if ($randomMatching) {
                // For random matching, ignore interest and gender completely
                $potentialMatches = $this->userRepository->findUsersForMatching(
                    '', // No interest filter
                    '', // No gender filter
                    $user->id,
                    100
                );
            } else {
                // If user has no specific interest (random matching), find more broadly
                $interest = $user->interest === 'all' || $user->interest === null ? '' : $user->interest;

                $potentialMatches = $this->userRepository->findUsersForMatching(
                    $interest,
                    $user->gender,
                    $user->id,
                    100
                );
            }

            // Additional filtering
            return $potentialMatches->filter(function ($potentialMatch) use ($user) {
                return $this->isCompatibleMatch($user, $potentialMatch);
            });
        });
    }

    private function isCompatibleMatch(User $user1, User $user2): bool
    {
        // Check if random matching is enabled globally
        $randomMatching = config('telegram.matching.random_matching', false);

        if (! $randomMatching) {
            // Normal matching logic - check gender/interest compatibility
            $user1AcceptsAll = $user1->interest === 'all' || $user1->interest === null;
            $user2AcceptsAll = $user2->interest === 'all' || $user2->interest === null;

            if (! $user1AcceptsAll && ! $user2AcceptsAll) {
                // Basic gender/interest compatibility for specific preferences
                if ($user1->gender !== $user2->interest || $user2->gender !== $user1->interest) {
                    return false;
                }
            } elseif ($user1AcceptsAll || $user2AcceptsAll) {
                // At least one user accepts random matching - more flexible matching
                // Still ensure basic compatibility exists
            }
        }
        // If random matching is enabled, skip gender/interest checks entirely

        // Age compatibility (configurable range)
        if ($user1->age && $user2->age) {
            $ageDiff = abs($user1->age - $user2->age);
            if ($ageDiff > 10) {
                return false;
            }
        }

        // Location compatibility (if both have locations)
        if ($user1->hasLocation() && $user2->hasLocation()) {
            $distance = $user1->distanceTo($user2);
            if ($distance > $user1->search_radius) {
                return false;
            }
        }

        // Check if users have already been paired recently
        $recentPair = $this->pairRepository->findPairBetweenUsers($user1->id, $user2->id);
        if ($recentPair && $recentPair->created_at->diffInHours(now()) < 24) {
            return false;
        }

        return true;
    }

    private function rankMatches(User $user, Collection $potentialMatches): Collection
    {
        return $potentialMatches->map(function ($match) use ($user) {
            $score = $this->calculateMatchScore($user, $match);
            $match->match_score = $score;

            return $match;
        })->sortByDesc('match_score');
    }

    private function calculateMatchScore(User $user, User $match): float
    {
        $score = 0;

        // Base compatibility score
        $score += 50;

        // Age compatibility bonus
        if ($user->age && $match->age) {
            $ageDiff = abs($user->age - $match->age);
            $score += max(0, 20 - $ageDiff); // Max 20 points for same age
        }

        // Location proximity bonus
        if ($user->hasLocation() && $match->hasLocation()) {
            $distance = $user->distanceTo($match);
            $score += max(0, 30 - ($distance / 10)); // Max 30 points for close proximity
        }

        // Activity level bonus
        if ($match->isActive()) {
            $score += 15;
        }

        // Premium user bonus
        if ($match->isPremium()) {
            $score += 10;
        }

        // Rating bonus
        $averageRating = $match->getAverageRating();
        if ($averageRating > 0) {
            $score += $averageRating * 5; // Max 25 points for 5-star rating
        }

        // Penalize users with many recent matches (to promote variety)
        $recentMatchCount = $this->pairRepository->countPairsByUser($match->id);
        if ($recentMatchCount > 10) {
            $score -= min(20, $recentMatchCount - 10);
        }

        return max(0, $score);
    }

    private function clearMatchCaches(int $userId): void
    {
        Cache::forget("potential_matches:user:{$userId}");
        Cache::forget('users_ready_for_matching:100');
        Cache::forget('matching_statistics');
    }
}
