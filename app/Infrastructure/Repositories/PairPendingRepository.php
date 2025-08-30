<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Repositories\PairPendingRepositoryInterface;
use App\Models\PairPending;
use Illuminate\Database\Eloquent\Collection;

/**
 * Pair Pending Repository Implementation
 *
 * Infrastructure layer implementation of PairPendingRepositoryInterface
 */
class PairPendingRepository implements PairPendingRepositoryInterface
{
    /**
     * Basic CRUD Operations
     */
    public function findById(int $id): ?object
    {
        return PairPending::find($id);
    }

    public function create(array $data): object
    {
        return PairPending::create($data);
    }

    public function update(object $pairPending, array $data): bool
    {
        return $pairPending->update($data);
    }

    public function delete(object $pairPending): bool
    {
        return $pairPending->delete();
    }

    /**
     * User Operations
     */
    public function findByUserId(int $userId): ?object
    {
        return PairPending::where('user_id', $userId)->first();
    }

    public function findPendingPairs(): Collection
    {
        return PairPending::orderBy('created_at', 'ASC')->get();
    }

    public function clearUserPendingPair(int $userId): bool
    {
        return PairPending::where('user_id', $userId)->delete() > 0;
    }

    /**
     * Matching Operations
     */
    public function findNextPendingPair(int $userId): ?object
    {
        return PairPending::where('user_id', '!=', $userId)
            ->orderBy('created_at', 'ASC')
            ->first();
    }

    public function countPendingPairs(): int
    {
        return PairPending::count();
    }

    public function findAvailableMatch(string $userGender, string $targetGender): ?object
    {
        return PairPending::where('gender', $targetGender)
            ->where('interest', $userGender)
            ->orderBy('created_at', 'ASC')
            ->first();
    }

    public function deleteByUserId(int $userId): bool
    {
        return PairPending::where('user_id', $userId)->delete() > 0;
    }
}
