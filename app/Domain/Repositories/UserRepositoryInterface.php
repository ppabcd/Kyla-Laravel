<?php

namespace App\Domain\Repositories;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * User Repository Interface
 *
 * Defines contract for user data access operations
 * Following Interface Segregation Principle
 */
interface UserRepositoryInterface
{
    /**
     * Basic CRUD Operations
     */
    public function findById(int $id): ?User;

    public function findByTelegramId(int $telegramId): ?User;

    public function create(array $data): User;

    public function update(User $user, array $data): bool;

    public function delete(User $user): bool;

    /**
     * Search and Query Operations
     */
    public function findByUsername(string $username): ?User;

    public function findByReferralCode(string $referralCode): ?User;

    public function findUsersForMatching(string $interest, string $gender, int $excludeUserId, int $limit = 50): Collection;

    public function searchUsers(array $criteria, int $page = 1, int $perPage = 20): LengthAwarePaginator;

    /**
     * Activity and Status Operations
     */
    public function updateLastActivity(User $user): bool;

    public function findActiveUsers(int $minutes = 30): Collection;

    public function findInactiveUsers(int $days = 7): Collection;

    /**
     * Premium and Balance Operations
     */
    public function findPremiumUsers(): Collection;

    public function findUsersWithBalance(float $minBalance = 0): Collection;

    public function updateBalance(User $user, float $amount, string $type, string $description): bool;

    /**
     * Ban and Moderation Operations
     */
    public function findBannedUsers(): Collection;

    public function banUser(User $user, string $reason, int $bannedBy): bool;

    public function unbanUser(User $user): bool;

    public function softBanUser(User $user, int $minutes): bool;

    /**
     * Statistics and Analytics
     */
    public function countActiveUsers(): int;

    public function countPremiumUsers(): int;

    public function countUsersByGender(): array;

    public function countUsersByRegion(): array;

    public function getUserStatistics(User $user): array;

    /**
     * Relationship Operations
     */
    public function findUsersByReferrer(int $referrerId): Collection;

    public function findUserMatches(User $user): Collection;

    public function findUserReports(User $user): Collection;

    /**
     * Bulk Operations
     */
    public function bulkUpdate(array $userIds, array $data): int;

    public function bulkDelete(array $userIds): int;

    /**
     * Verification Operations
     */
    public function markAsVerified(User $user): bool;

    public function findUnverifiedUsers(): Collection;

    /**
     * Location Operations
     */
    public function findUsersNearLocation(float $latitude, float $longitude, int $radiusKm): Collection;

    public function updateUserLocation(User $user, float $latitude, float $longitude): bool;
}
