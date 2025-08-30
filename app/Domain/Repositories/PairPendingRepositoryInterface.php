<?php

namespace App\Domain\Repositories;

use Illuminate\Database\Eloquent\Collection;

/**
 * Pair Pending Repository Interface
 *
 * Defines contract for pair pending data access operations
 */
interface PairPendingRepositoryInterface
{
    /**
     * Basic CRUD Operations
     */
    public function findById(int $id): ?object;

    public function create(array $data): object;

    public function update(object $pairPending, array $data): bool;

    public function delete(object $pairPending): bool;

    /**
     * User Operations
     */
    public function findByUserId(int $userId): ?object;

    public function findPendingPairs(): Collection;

    public function clearUserPendingPair(int $userId): bool;

    /**
     * Matching Operations
     */
    public function findNextPendingPair(int $userId): ?object;

    public function countPendingPairs(): int;

    public function findAvailableMatch(string $userGender, string $targetGender): ?object;

    public function deleteByUserId(int $userId): bool;
}
