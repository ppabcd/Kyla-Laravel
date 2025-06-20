<?php

namespace App\Domain\Repositories;

use Illuminate\Database\Eloquent\Collection;

/**
 * Rating Repository Interface
 * 
 * Defines contract for rating data access operations
 */
interface RatingRepositoryInterface
{
    /**
     * Basic CRUD Operations
     */
    public function findById(int $id): ?object;
    public function create(array $data): object;
    public function update(object $rating, array $data): bool;
    public function delete(object $rating): bool;

    /**
     * User Rating Operations
     */
    public function findByUserId(int $userId): Collection;
    public function findUserRatings(int $userId): Collection;
    public function getAverageRating(int $userId): float;

    /**
     * Rating Statistics
     */
    public function getTotalRatings(): int;
    public function getRatingDistribution(): array;
}
