<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Repositories\PairPendingRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

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
        return DB::table('pair_pendings')->where('id', $id)->first();
    }

    public function create(array $data): object
    {
        $id = DB::table('pair_pendings')->insertGetId($data);
        return $this->findById($id);
    }

    public function update(object $pairPending, array $data): bool
    {
        return DB::table('pair_pendings')
            ->where('id', $pairPending->id)
            ->update($data) > 0;
    }

    public function delete(object $pairPending): bool
    {
        return DB::table('pair_pendings')
            ->where('id', $pairPending->id)
            ->delete() > 0;
    }

    /**
     * User Operations
     */
    public function findByUserId(int $userId): ?object
    {
        return DB::table('pair_pendings')
            ->where('user_id', $userId)
            ->first();
    }

    public function findPendingPairs(): Collection
    {
        return collect(DB::table('pair_pendings')
            ->orderBy('created_at', 'ASC')
            ->get());
    }

    public function clearUserPendingPair(int $userId): bool
    {
        return DB::table('pair_pendings')
            ->where('user_id', $userId)
            ->delete() > 0;
    }

    /**
     * Matching Operations
     */
    public function findNextPendingPair(int $userId): ?object
    {
        return DB::table('pair_pendings')
            ->where('user_id', '!=', $userId)
            ->orderBy('created_at', 'ASC')
            ->first();
    }

    public function countPendingPairs(): int
    {
        return DB::table('pair_pendings')->count();
    }
}
