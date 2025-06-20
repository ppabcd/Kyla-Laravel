<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\Pair;
use App\Domain\Entities\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Pair Repository Interface
 * 
 * Defines contract for pair/matching data access operations
 */
interface PairRepositoryInterface
{
    /**
     * Basic CRUD Operations
     */
    public function findById(int $id): ?Pair;
    public function create(array $data): Pair;
    public function update(Pair $pair, array $data): bool;
    public function delete(Pair $pair): bool;

    /**
     * User Pair Operations
     */
    public function findActivePairByUserId(int $userId): ?Pair;
    public function findPairsByUserId(int $userId): Collection;
    public function findPairBetweenUsers(int $userId1, int $userId2): ?Pair;
    public function createPair(User $user, User $partner): Pair;

    /**
     * Pair Management Operations
     */
    public function startPair(Pair $pair): bool;
    public function endPair(Pair $pair, int $endedBy, ?string $reason = null): bool;
    public function findExpiredPairs(int $maxDurationMinutes): Collection;
    public function findInactivePairs(int $inactiveMinutes): Collection;

    /**
     * Search and Query Operations
     */
    public function findActivePairs(): Collection;
    public function findEndedPairs(): Collection;
    public function findPairsByStatus(string $status): Collection;
    public function searchPairs(array $criteria, int $page = 1, int $perPage = 20): LengthAwarePaginator;

    /**
     * Rating Operations
     */
    public function addRating(Pair $pair, int $raterUserId, float $rating): bool;
    public function findPairsWithRatings(): Collection;
    public function findUnratedPairs(): Collection;

    /**
     * Statistics Operations
     */
    public function countActivePairs(): int;
    public function countTotalPairs(): int;
    public function countPairsByUser(int $userId): int;
    public function getAveragePairDuration(): float;
    public function getPairStatistics(): array;

    /**
     * Conversation Operations
     */
    public function incrementConversationCount(Pair $pair): bool;
    public function updateLastMessageTime(Pair $pair): bool;
    public function findPairsWithoutConversation(): Collection;

    /**
     * Bulk Operations
     */
    public function bulkEndPairs(array $pairIds, int $endedBy, string $reason): int;
    public function cleanupEndedPairs(int $daysOld): int;
}
