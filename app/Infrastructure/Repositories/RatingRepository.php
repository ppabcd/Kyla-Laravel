<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Repositories\RatingRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Rating Repository Implementation
 *
 * Infrastructure layer implementation of RatingRepositoryInterface
 */
class RatingRepository implements RatingRepositoryInterface
{
    /**
     * Basic CRUD Operations
     */
    public function findById(int $id): ?object
    {
        return DB::table('ratings')->where('id', $id)->first();
    }

    public function create(array $data): object
    {
        $id = DB::table('ratings')->insertGetId($data);

        return $this->findById($id);
    }

    public function update(object $rating, array $data): bool
    {
        return DB::table('ratings')
            ->where('id', $rating->id)
            ->update($data) > 0;
    }

    public function delete(object $rating): bool
    {
        return DB::table('ratings')
            ->where('id', $rating->id)
            ->delete() > 0;
    }

    /**
     * User Rating Operations
     */
    public function findByUserId(int $userId): Collection
    {
        return collect(DB::table('ratings')
            ->where('user_id', $userId)
            ->get());
    }

    public function findUserRatings(int $userId): Collection
    {
        return collect(DB::table('ratings')
            ->where('rated_user_id', $userId)
            ->get());
    }

    public function getAverageRating(int $userId): float
    {
        $average = DB::table('ratings')
            ->where('rated_user_id', $userId)
            ->avg('rating');

        return $average ? (float) $average : 0.0;
    }

    public function getAverageRatingByUserId(int $userId): float
    {
        return $this->getAverageRating($userId);
    }

    /**
     * Rating Statistics
     */
    public function getTotalRatings(): int
    {
        return DB::table('ratings')->count();
    }

    public function getRatingDistribution(): array
    {
        $distribution = DB::table('ratings')
            ->select('rating', DB::raw('COUNT(*) as count'))
            ->groupBy('rating')
            ->orderBy('rating')
            ->get();

        return $distribution->pluck('count', 'rating')->toArray();
    }
}
