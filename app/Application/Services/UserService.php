<?php

namespace App\Application\Services;

use App\Domain\Entities\User;
use App\Domain\Repositories\PairRepositoryInterface;
use App\Domain\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class UserService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PairRepositoryInterface $pairRepository
    ) {}

    public function findOrCreateUser(array $telegramData): User
    {
        $user = $this->userRepository->findByTelegramId($telegramData['id']);

        if (! $user) {
            $user = $this->userRepository->create([
                'telegram_id' => $telegramData['id'],
                'first_name' => $telegramData['first_name'] ?? '',
                'last_name' => $telegramData['last_name'] ?? null,
                'username' => $telegramData['username'] ?? null,
                'language_code' => $telegramData['language_code'] ?? 'en',
                'last_activity_at' => now(),
                'settings' => [
                    'notifications' => true,
                    'privacy' => 'public',
                    'safe_mode' => true,
                ],
            ]);

            Log::info('New user created', ['telegram_id' => $telegramData['id']]);
        } else {
            $this->userRepository->updateLastActivity($user);
        }

        return $user;
    }

    public function updateLastActivity(int $userId): bool
    {
        $user = $this->userRepository->findById($userId);

        if (! $user) {
            return false;
        }

        return $this->userRepository->updateLastActivity($user);
    }

    public function updateUser(int $userId, array $data): bool
    {
        $user = $this->userRepository->findById($userId);

        if (! $user) {
            return false;
        }

        return $this->userRepository->update($user, $data);
    }

    public function updateUserProfile(User $user, array $data): bool
    {
        // Auto-set gender_icon when gender is provided
        if (array_key_exists('gender', $data) && ! array_key_exists('gender_icon', $data)) {
            $data['gender_icon'] = match ($data['gender']) {
                'male' => '👨',
                'female' => '👩',
                default => null,
            };
        }

        $updated = $this->userRepository->update($user, $data);

        if ($updated) {
            $this->clearUserCache($user);
            Log::info('User profile updated', ['user_id' => $user->id]);
        }

        return $updated;
    }

    public function banUser(User $user, string $reason): bool
    {
        $updated = $this->userRepository->update($user, [
            'is_banned' => true,
            'banned_reason' => $reason,
        ]);

        if ($updated) {
            // End any active pairs
            $activePair = $this->pairRepository->findActivePairByUserId($user->id);
            if ($activePair) {
                $this->pairRepository->endPair($activePair, $user->id, 'User banned');
            }

            $this->clearUserCache($user);
            Log::warning('User banned', [
                'user_id' => $user->id,
                'reason' => $reason,
            ]);
        }

        return $updated;
    }

    public function unbanUser(User $user): bool
    {
        $updated = $this->userRepository->update($user, [
            'is_banned' => false,
            'banned_reason' => null,
        ]);

        if ($updated) {
            $this->clearUserCache($user);
            Log::info('User unbanned', ['user_id' => $user->id]);
        }

        return $updated;
    }

    public function upgradeToPremium(User $user, int $days = 30): bool
    {
        $expiresAt = now()->addDays($days);

        $updated = $this->userRepository->update($user, [
            'is_premium' => true,
            'premium_expires_at' => $expiresAt,
        ]);

        if ($updated) {
            $this->clearUserCache($user);
            Log::info('User upgraded to premium', [
                'user_id' => $user->id,
                'expires_at' => $expiresAt,
            ]);
        }

        return $updated;
    }

    public function findMatchableUsers(User $user): array
    {
        $cacheKey = "matchable_users_{$user->id}";

        return Cache::remember($cacheKey, 300, function () use ($user) {
            $users = $this->userRepository->findUsersForMatching(
                $user->interest,
                $user->gender,
                $user->id
            );

            return $users->filter(function ($potentialUser) use ($user) {
                // Additional filtering logic
                return $this->isCompatibleMatch($user, $potentialUser);
            })->values()->all();
        });
    }

    public function isCompatibleMatch(User $user1, User $user2): bool
    {
        // Basic compatibility check
        if ($user1->gender !== $user2->interest || $user2->gender !== $user1->interest) {
            return false;
        }

        // Age compatibility (optional)
        if ($user1->age && $user2->age) {
            $ageDiff = abs($user1->age - $user2->age);
            if ($ageDiff > 10) {
                return false;
            }
        }

        // Location compatibility (optional)
        if ($user1->location && $user2->location) {
            // Simple location matching - can be enhanced with geolocation
            if ($user1->location !== $user2->location) {
                return false;
            }
        }

        return true;
    }

    public function getActiveUsersCount(): int
    {
        return Cache::remember('active_users_count', 300, function () {
            return $this->userRepository->countActiveUsers();
        });
    }

    public function getPremiumUsersCount(): int
    {
        return Cache::remember('premium_users_count', 300, function () {
            return $this->userRepository->countPremiumUsers();
        });
    }

    public function getUserStats(User $user): array
    {
        $pairs = $this->pairRepository->findPairsByUserId($user->id);
        $activePairs = $pairs->where('status', 'active')->count();
        $totalPairs = $pairs->count();

        return [
            'total_pairs' => $totalPairs,
            'active_pairs' => $activePairs,
            'is_premium' => $user->isPremium(),
            'is_banned' => $user->is_banned,
            'last_activity' => $user->last_activity_at,
            'created_at' => $user->created_at,
        ];
    }

    public function getUserStatistics(): array
    {
        return Cache::remember('user_statistics', 600, function () {
            return [
                'total_users' => $this->userRepository->countTotalUsers(),
                'active_users' => $this->userRepository->countActiveUsers(),
                'premium_users' => $this->userRepository->countPremiumUsers(),
                'banned_users' => $this->userRepository->countBannedUsers(),
                'users_today' => $this->userRepository->countUsersRegisteredToday(),
                'premium_percentage' => $this->calculatePremiumPercentage(),
            ];
        });
    }

    private function calculatePremiumPercentage(): float
    {
        $totalUsers = $this->userRepository->countTotalUsers();
        if ($totalUsers === 0) {
            return 0.0;
        }

        $premiumUsers = $this->userRepository->countPremiumUsers();

        return ($premiumUsers / $totalUsers) * 100;
    }

    private function clearUserCache(User $user): void
    {
        Cache::forget("matchable_users_{$user->id}");
        Cache::forget("user_stats_{$user->id}");
    }
}
