<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Repositories\UserRepositoryInterface;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * User Repository Implementation
 *
 * Infrastructure layer implementation of UserRepositoryInterface
 * Following Repository pattern and Single Responsibility Principle
 */
class UserRepository implements UserRepositoryInterface
{
    public function __construct()
    {
        logger('UserRepository initialized');
    }

    /**
     * Basic CRUD Operations
     */
    public function findById(int $id): ?User
    {
        return Cache::remember("user:{$id}", 300, function () use ($id) {
            return User::find($id);
        });
    }

    public function findByTelegramId(int $telegramId): ?User
    {
        return Cache::remember("user:telegram:{$telegramId}", 300, function () use ($telegramId) {
            return User::where('telegram_id', $telegramId)->first();
        });
    }

    public function create(array $data): User
    {
        $user = User::create($data);

        // Clear relevant caches
        $this->clearUserCaches($user);

        return $user;
    }

    public function update(User $user, array $data): bool
    {
        $updated = $user->update($data);

        if ($updated) {
            $this->clearUserCaches($user);
        }

        return $updated;
    }

    public function delete(User $user): bool
    {
        $deleted = $user->delete();

        if ($deleted) {
            $this->clearUserCaches($user);
        }

        return $deleted;
    }

    /**
     * Search and Query Operations
     */
    public function findByUsername(string $username): ?User
    {
        return User::where('username', $username)->first();
    }

    public function findByReferralCode(string $referralCode): ?User
    {
        return User::where('referral_code', $referralCode)->first();
    }

    public function findUsersForMatching(string $interest, string $gender, int $excludeUserId, int $limit = 50): Collection
    {
        $query = User::where('id', '!=', $excludeUserId)
            ->where('is_banned', false)
            ->where('is_searching', true);

        // Apply gender filter only if specified
        if ($gender !== '') {
            $query->where('interest', $gender);
        }

        // Apply interest filter only if specified
        if ($interest !== '') {
            $query->where('gender', $interest);
        }

        // Check for soft ban
        $query->where(function ($q) {
            $q->whereNull('soft_banned_until')
                ->orWhere('soft_banned_until', '<', now());
        });

        // Exclude users who already have active pairs
        $query->whereDoesntHave('pairsAsFirst', function ($subQuery) {
            $subQuery->where('active', true);
        })
            ->whereDoesntHave('pairsAsSecond', function ($subQuery) {
                $subQuery->where('active', true);
            });

        return $query->inRandomOrder()
            ->limit($limit)
            ->get();
    }

    public function searchUsers(array $criteria, int $page = 1, int $perPage = 20): LengthAwarePaginator
    {
        $query = User::query();

        foreach ($criteria as $field => $value) {
            if ($value !== null && $value !== '') {
                switch ($field) {
                    case 'telegram_id':
                    case 'age':
                        $query->where($field, $value);
                        break;
                    case 'gender':
                    case 'interest':
                    case 'region':
                    case 'verification_status':
                        $query->where($field, $value);
                        break;
                    case 'name':
                        $query->where(function ($q) use ($value) {
                            $q->where('first_name', 'like', "%{$value}%")
                                ->orWhere('last_name', 'like', "%{$value}%")
                                ->orWhere('username', 'like', "%{$value}%");
                        });
                        break;
                    case 'is_premium':
                    case 'is_banned':
                    case 'visibility':
                        $query->where($field, (bool) $value);
                        break;
                    case 'min_balance':
                        $query->where('balance', '>=', $value);
                        break;
                    case 'max_balance':
                        $query->where('balance', '<=', $value);
                        break;
                }
            }
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Activity and Status Operations
     */
    public function updateLastActivity(User $user): bool
    {
        return $this->update($user, ['last_activity_at' => now()]);
    }

    public function findActiveUsers(int $minutes = 30): Collection
    {
        return User::where('last_activity_at', '>=', now()->subMinutes($minutes))
            ->where('is_banned', false)
            ->get();
    }

    public function findInactiveUsers(int $days = 7): Collection
    {
        return User::where('last_activity_at', '<', now()->subDays($days))
            ->orWhereNull('last_activity_at')
            ->get();
    }

    /**
     * Premium and Balance Operations
     */
    public function findPremiumUsers(): Collection
    {
        return User::where('is_premium', true)
            ->where(function ($query) {
                $query->whereNull('premium_expires_at')
                    ->orWhere('premium_expires_at', '>', now());
            })
            ->get();
    }

    public function findUsersWithBalance(float $minBalance = 0): Collection
    {
        return User::where('balance', '>=', $minBalance)->get();
    }

    public function updateBalance(User $user, float $amount, string $type, string $description): bool
    {
        return DB::transaction(function () use ($user, $amount, $type, $description) {
            $previousBalance = $user->balance;

            if ($type === 'credit') {
                $user->incrementBalance($amount, $description);
            } elseif ($type === 'debit') {
                if (! $user->decrementBalance($amount, $description)) {
                    return false;
                }
            }

            return true;
        });
    }

    /**
     * Ban and Moderation Operations
     */
    public function findBannedUsers(): Collection
    {
        return User::where('is_banned', true)->get();
    }

    public function banUser(User $user, string $reason, int $bannedBy): bool
    {
        $user->ban($reason);

        // Log the ban action
        activity()
            ->performedOn($user)
            ->causedBy($bannedBy)
            ->withProperties(['reason' => $reason])
            ->log('user_banned');

        return true;
    }

    public function unbanUser(User $user): bool
    {
        $user->unban();

        return true;
    }

    public function softBanUser(User $user, int $minutes): bool
    {
        $user->softBan($minutes);

        return true;
    }

    /**
     * Statistics and Analytics Operations
     */
    public function countActiveUsers(): int
    {
        return Cache::remember('count_active_users', 300, function () {
            return User::where('last_activity_at', '>=', now()->subMinutes(30))
                ->where('is_banned', false)
                ->count();
        });
    }

    public function countPremiumUsers(): int
    {
        return Cache::remember('count_premium_users', 300, function () {
            return User::where('is_premium', true)
                ->where(function ($query) {
                    $query->whereNull('premium_expires_at')
                        ->orWhere('premium_expires_at', '>', now());
                })
                ->count();
        });
    }

    public function countUsersByGender(): array
    {
        return Cache::remember('count_users_by_gender', 3600, function () {
            return User::select('gender', DB::raw('count(*) as count'))
                ->whereNotNull('gender')
                ->groupBy('gender')
                ->pluck('count', 'gender')
                ->toArray();
        });
    }

    public function countUsersByRegion(): array
    {
        return Cache::remember('count_users_by_region', 3600, function () {
            return User::select('region', DB::raw('count(*) as count'))
                ->whereNotNull('region')
                ->groupBy('region')
                ->pluck('count', 'region')
                ->toArray();
        });
    }

    public function getUserStatistics(User $user): array
    {
        return [
            'total_pairs' => $user->allPairs()->count(),
            'active_pairs' => $user->pairs()->where('status', 'active')->count(),
            'total_messages' => $user->total_messages_sent,
            'total_time_chatting' => $user->total_time_chatting,
            'average_rating' => $user->getAverageRating(),
            'is_premium' => $user->isPremium(),
            'is_banned' => $user->isBanned(),
            'last_activity' => $user->last_activity_at,
            'referrals_count' => $user->total_referrals,
        ];
    }

    /**
     * Relationship Operations
     */
    public function findUsersByReferrer(int $referrerId): Collection
    {
        return User::where('referred_by', $referrerId)->get();
    }

    public function findUserMatches(User $user): Collection
    {
        return $user->allPairs()->with(['user', 'partner'])->get()
            ->map(function ($pair) use ($user) {
                return $pair->getOtherUser($user->id);
            })
            ->filter();
    }

    public function findUserReports(User $user): Collection
    {
        return $user->reports()->with('reporter')->get();
    }

    /**
     * Bulk Operations
     */
    public function bulkUpdate(array $userIds, array $data): int
    {
        $updated = User::whereIn('id', $userIds)->update($data);

        // Clear caches for all affected users
        foreach ($userIds as $userId) {
            Cache::forget("user:{$userId}");
        }

        return $updated;
    }

    public function bulkDelete(array $userIds): int
    {
        $deleted = User::whereIn('id', $userIds)->delete();

        // Clear caches for all affected users
        foreach ($userIds as $userId) {
            Cache::forget("user:{$userId}");
        }

        return $deleted;
    }

    /**
     * Verification Operations
     */
    public function markAsVerified(User $user): bool
    {
        return $this->update($user, ['verification_status' => 'verified']);
    }

    public function findUnverifiedUsers(): Collection
    {
        return User::where('verification_status', '!=', 'verified')
            ->orWhereNull('verification_status')
            ->get();
    }

    /**
     * Location Operations
     */
    public function findUsersNearLocation(float $latitude, float $longitude, int $radiusKm): Collection
    {
        // Using Haversine formula for distance calculation
        return User::select('*')
            ->selectRaw(
                '( 6371 * acos( cos( radians(?) ) * cos( radians( location_latitude ) ) * cos( radians( location_longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( location_latitude ) ) ) ) AS distance',
                [$latitude, $longitude, $latitude]
            )
            ->whereNotNull('location_latitude')
            ->whereNotNull('location_longitude')
            ->having('distance', '<=', $radiusKm)
            ->orderBy('distance')
            ->get();
    }

    public function updateUserLocation(User $user, float $latitude, float $longitude): bool
    {
        return $this->update($user, [
            'location_latitude' => $latitude,
            'location_longitude' => $longitude,
        ]);
    }

    /**
     * Private Helper Methods
     */
    private function clearUserCaches(User $user): void
    {
        Cache::forget("user:{$user->id}");
        Cache::forget("user:telegram:{$user->telegram_id}");
        Cache::forget('count_active_users');
        Cache::forget('count_premium_users');
        Cache::forget('count_users_by_gender');
        Cache::forget('count_users_by_region');
    }

    /**
     * Dashboard Statistics Methods
     */
    public function getTotalUsers(): int
    {
        try {
            return Cache::remember('users:total_count', 300, function () {
                return User::count();
            });
        } catch (\Exception $e) {
            logger('Cache error in getTotalUsers: '.$e->getMessage());

            return User::count();
        }
    }

    public function getActiveUsersCount(int $days = 7): int
    {
        try {
            return Cache::remember("users:active_count:{$days}", 300, function () use ($days) {
                return User::where('last_activity_at', '>=', now()->subDays($days))
                    ->where('is_banned', false)
                    ->count();
            });
        } catch (\Exception $e) {
            logger('Cache error in getActiveUsersCount: '.$e->getMessage());

            return User::where('last_activity_at', '>=', now()->subDays($days))
                ->where('is_banned', false)
                ->count();
        }
    }

    public function getNewUsersCount(int $days = 1): int
    {
        try {
            return Cache::remember("users:new_count:{$days}", 300, function () use ($days) {
                return User::where('created_at', '>=', now()->subDays($days))->count();
            });
        } catch (\Exception $e) {
            logger('Cache error in getNewUsersCount: '.$e->getMessage());

            return User::where('created_at', '>=', now()->subDays($days))->count();
        }
    }

    public function getBannedUsersCount(?int $days = null): int
    {
        try {
            return Cache::remember('users:banned_count:'.($days ?? 'all'), 300, function () use ($days) {
                $query = User::where('is_banned', true);
                if ($days) {
                    $query->where('banned_at', '>=', now()->subDays($days));
                }

                return $query->count();
            });
        } catch (\Exception $e) {
            logger('Cache error in getBannedUsersCount: '.$e->getMessage());
            // Fallback to direct database query
            $query = User::where('is_banned', true);
            if ($days) {
                $query->where('banned_at', '>=', now()->subDays($days));
            }

            return $query->count();
        }
    }

    public function getPremiumUsersCount(): int
    {
        return Cache::remember('users:premium_count', 300, function () {
            return User::where('is_premium', true)
                ->where(function ($query) {
                    $query->whereNull('premium_expires_at')
                        ->orWhere('premium_expires_at', '>', now());
                })
                ->count();
        });
    }

    public function getSearchQueueLength(): int
    {
        return Cache::remember('users:queue_length', 60, function () {
            return User::where('is_searching', true)
                ->where('is_banned', false)
                ->whereDoesntHave('pairs', function ($query) {
                    $query->where('status', 'active');
                })
                ->count();
        });
    }

    public function getFilteredUsers(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = User::query();

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('telegram_id', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['status'])) {
            switch ($filters['status']) {
                case 'active':
                    $query->where('is_banned', false);
                    break;
                case 'banned':
                    $query->where('is_banned', true);
                    break;
                case 'premium':
                    $query->where('is_premium', true);
                    break;
            }
        }

        if (! empty($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function getRecentUsers(int $limit = 10): Collection
    {
        return Cache::remember("users:recent:{$limit}", 300, function () use ($limit) {
            return User::orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();
        });
    }

    public function getGenderDistribution(): array
    {
        return Cache::remember('users:gender_distribution', 600, function () {
            return User::selectRaw('gender, COUNT(*) as count')
                ->groupBy('gender')
                ->pluck('count', 'gender')
                ->toArray();
        });
    }

    public function getLanguageDistribution(): array
    {
        return Cache::remember('users:language_distribution', 600, function () {
            return User::selectRaw('language_code, COUNT(*) as count')
                ->groupBy('language_code')
                ->pluck('count', 'language_code')
                ->toArray();
        });
    }

    public function getCountryDistribution(): array
    {
        return Cache::remember('users:country_distribution', 600, function () {
            return User::selectRaw('language_code, COUNT(*) as count')
                ->whereNotNull('language_code')
                ->groupBy('language_code')
                ->pluck('count', 'language_code')
                ->toArray();
        });
    }

    public function getPremiumDistribution(): array
    {
        return Cache::remember('users:premium_distribution', 600, function () {
            return [
                'premium' => User::where('is_premium', true)->count(),
                'regular' => User::where('is_premium', false)->count(),
            ];
        });
    }

    public function getNewUsersCountByDate(Carbon $date): int
    {
        return User::whereDate('created_at', $date)->count();
    }

    public function getBannedUsers(int $limit = 50): Collection
    {
        return User::where('is_banned', true)
            ->orderBy('banned_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getTopSpenders(int $limit = 10): Collection
    {
        return Cache::remember("users:top_spenders:{$limit}", 600, function () use ($limit) {
            return User::selectRaw('users.*, SUM(balance_transactions.amount) as total_spent, COUNT(balance_transactions.id) as transaction_count')
                ->leftJoin('balance_transactions', 'users.id', '=', 'balance_transactions.user_id')
                ->where('balance_transactions.amount', '>', 0)
                ->groupBy('users.id')
                ->orderBy('total_spent', 'desc')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Alias methods for compatibility
     */
    public function countTotalUsers(): int
    {
        return $this->getTotalUsers();
    }

    public function countBannedUsers(): int
    {
        return $this->getBannedUsersCount();
    }

    public function countUsersRegisteredToday(): int
    {
        return $this->getNewUsersCount(1);
    }
}
