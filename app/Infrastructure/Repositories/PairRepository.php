<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\Pair;
use App\Domain\Entities\User;
use App\Domain\Repositories\PairRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Pair Repository Implementation
 *
 * Infrastructure layer implementation of PairRepositoryInterface
 */
class PairRepository implements PairRepositoryInterface
{
    /**
     * Basic CRUD Operations
     */
    public function findById(int $id): ?Pair
    {
        return Cache::remember("pair:{$id}", 300, function () use ($id) {
            return Pair::with(['user', 'partner'])->find($id);
        });
    }

    public function create(array $data): Pair
    {
        $pair = Pair::create($data);
        $this->clearPairCaches($pair);

        return $pair;
    }

    public function update(Pair $pair, array $data): bool
    {
        $updated = $pair->update($data);

        if ($updated) {
            $this->clearPairCaches($pair);
        }

        return $updated;
    }

    public function delete(Pair $pair): bool
    {
        $deleted = $pair->delete();

        if ($deleted) {
            $this->clearPairCaches($pair);
        }

        return $deleted;
    }

    /**
     * User Pair Operations
     */
    public function findActivePairByUserId(int $userId): ?Pair
    {
        return Cache::remember("active_pair:user:{$userId}", 300, function () use ($userId) {
            return Pair::where('status', 'active')
                ->where(function ($query) use ($userId) {
                    $query->where('user_id', $userId)
                        ->orWhere('partner_id', $userId);
                })
                ->with(['user', 'partner'])
                ->first();
        });
    }

    public function findPairsByUserId(int $userId): Collection
    {
        return Pair::where(function ($query) use ($userId) {
            $query->where('user_id', $userId)
                ->orWhere('partner_id', $userId);
        })
            ->with(['user', 'partner'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findPairBetweenUsers(int $userId1, int $userId2): ?Pair
    {
        return Pair::where(function ($query) use ($userId1, $userId2) {
            $query->where('user_id', $userId1)->where('partner_id', $userId2);
        })->orWhere(function ($query) use ($userId1, $userId2) {
            $query->where('user_id', $userId2)->where('partner_id', $userId1);
        })
            ->with(['user', 'partner'])
            ->orderBy('created_at', 'desc')
            ->first();
    }

    public function createPair(User $user, User $partner): Pair
    {
        return $this->create([
            'user_id' => $user->id,
            'partner_id' => $partner->id,
            'status' => 'active',
            'active' => true,
            'started_at' => now(),
        ]);
    }

    /**
     * Pair Management Operations
     */
    public function startPair(Pair $pair): bool
    {
        return $this->update($pair, [
            'status' => 'active',
            'started_at' => now(),
        ]);
    }

    public function endPair(Pair $pair, int $endedBy, ?string $reason = null): bool
    {
        return $this->update($pair, [
            'status' => 'ended',
            'active' => false,
            'ended_at' => now(),
            'ended_by_user_id' => $endedBy,
            'ended_reason' => $reason,
        ]);
    }

    public function findExpiredPairs(int $maxDurationMinutes): Collection
    {
        return Pair::where('status', 'active')
            ->where('started_at', '<=', now()->subMinutes($maxDurationMinutes))
            ->with(['user', 'partner'])
            ->get();
    }

    public function findInactivePairs(int $inactiveMinutes): Collection
    {
        return Pair::where('status', 'active')
            ->where(function ($query) use ($inactiveMinutes) {
                $query->where('last_message_at', '<=', now()->subMinutes($inactiveMinutes))
                    ->orWhereNull('last_message_at');
            })
            ->with(['user', 'partner'])
            ->get();
    }

    /**
     * Search and Query Operations
     */
    public function findActivePairs(): Collection
    {
        return Cache::remember('active_pairs', 300, function () {
            return Pair::where('status', 'active')
                ->with(['user', 'partner'])
                ->get();
        });
    }

    public function findEndedPairs(): Collection
    {
        return Pair::where('status', 'ended')
            ->with(['user', 'partner', 'endedByUser'])
            ->orderBy('ended_at', 'desc')
            ->get();
    }

    public function findPairsByStatus(string $status): Collection
    {
        return Pair::where('status', $status)
            ->with(['user', 'partner'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findRecentPairs(int $limit = 10): Collection
    {
        return Pair::with(['user', 'partner'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function searchPairs(array $criteria, int $page = 1, int $perPage = 20): LengthAwarePaginator
    {
        $query = Pair::with(['user', 'partner', 'endedByUser']);

        foreach ($criteria as $field => $value) {
            if ($value !== null && $value !== '') {
                switch ($field) {
                    case 'status':
                        $query->where('status', $value);
                        break;
                    case 'user_id':
                    case 'partner_id':
                    case 'ended_by':
                        $query->where($field, $value);
                        break;
                    case 'start_date':
                        $query->where('started_at', '>=', $value);
                        break;
                    case 'end_date':
                        $query->where('ended_at', '<=', $value);
                        break;
                    case 'min_duration':
                        // Skip TIMESTAMPDIFF for now - will be filtered in PHP
                        break;
                    case 'max_duration':
                        // Skip TIMESTAMPDIFF for now - will be filtered in PHP
                        break;
                    case 'has_rating':
                        if ($value) {
                            $query->where(function ($q) {
                                $q->whereNotNull('rating_user')
                                    ->orWhereNotNull('rating_partner');
                            });
                        } else {
                            $query->whereNull('rating_user')
                                ->whereNull('rating_partner');
                        }
                        break;
                }
            }
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Rating Operations
     */
    public function addRating(Pair $pair, int $raterUserId, float $rating): bool
    {
        if ($raterUserId === $pair->user_id) {
            return $this->update($pair, ['rating_user' => $rating]);
        } elseif ($raterUserId === $pair->partner_id) {
            return $this->update($pair, ['rating_partner' => $rating]);
        }

        return false;
    }

    public function findPairsWithRatings(): Collection
    {
        return Pair::where(function ($query) {
            $query->whereNotNull('rating_user')
                ->orWhereNotNull('rating_partner');
        })
            ->with(['user', 'partner'])
            ->get();
    }

    public function findUnratedPairs(): Collection
    {
        return Pair::where('status', 'ended')
            ->whereNull('rating_user')
            ->whereNull('rating_partner')
            ->with(['user', 'partner'])
            ->get();
    }

    /**
     * Statistics Operations
     */
    public function countActivePairs(): int
    {
        return Cache::remember('count_active_pairs', 300, function () {
            return Pair::where('status', 'active')->count();
        });
    }

    public function countTotalPairs(): int
    {
        return Cache::remember('count_total_pairs', 3600, function () {
            return Pair::count();
        });
    }

    public function countPairsByUser(int $userId): int
    {
        return Pair::where(function ($query) use ($userId) {
            $query->where('user_id', $userId)
                ->orWhere('partner_id', $userId);
        })->count();
    }

    public function getAveragePairDuration(): float
    {
        return Cache::remember('avg_pair_duration', 3600, function () {
            // Use database-agnostic approach
            $pairs = DB::table('pairs')
                ->whereNotNull('started_at')
                ->whereNotNull('ended_at')
                ->select(['started_at', 'ended_at'])
                ->get();

            if ($pairs->isEmpty()) {
                return 0.0;
            }

            $totalMinutes = $pairs->sum(function ($pair) {
                $start = Carbon::parse($pair->started_at);
                $end = Carbon::parse($pair->ended_at);

                return $start->diffInMinutes($end);
            });

            return $totalMinutes / $pairs->count();
        });
    }

    public function getPairStatistics(): array
    {
        return Cache::remember('pair_statistics', 3600, function () {
            return [
                'total_pairs' => $this->countTotalPairs(),
                'active_pairs' => $this->countActivePairs(),
                'ended_pairs' => Pair::where('status', 'ended')->count(),
                'average_duration_minutes' => $this->getAveragePairDuration(),
                'pairs_with_ratings' => Pair::whereNotNull('ended_at')->count(),
                'average_rating' => 0.0, // Placeholder - ratings system not implemented yet
            ];
        });
    }

    /**
     * Conversation Operations
     */
    public function incrementConversationCount(Pair $pair): bool
    {
        return $this->update($pair, [
            'conversation_count' => $pair->conversation_count + 1,
            'last_message_at' => now(),
        ]);
    }

    public function updateLastMessageTime(Pair $pair): bool
    {
        return $this->update($pair, ['last_message_at' => now()]);
    }

    public function findPairsWithoutConversation(): Collection
    {
        return Pair::where('status', 'active')
            ->where('conversation_count', 0)
            ->where('started_at', '<=', now()->subMinutes(30))
            ->with(['user', 'partner'])
            ->get();
    }

    /**
     * Bulk Operations
     */
    public function bulkEndPairs(array $pairIds, int $endedBy, string $reason): int
    {
        $updated = Pair::whereIn('id', $pairIds)
            ->where('status', 'active')
            ->update([
                'status' => 'ended',
                'ended_at' => now(),
                'ended_by' => $endedBy,
                'end_reason' => $reason,
            ]);

        // Clear caches
        foreach ($pairIds as $pairId) {
            Cache::forget("pair:{$pairId}");
        }
        Cache::forget('active_pairs');
        Cache::forget('count_active_pairs');

        return $updated;
    }

    public function cleanupEndedPairs(int $daysOld): int
    {
        $cutoffDate = now()->subDays($daysOld);

        return Pair::where('status', 'ended')
            ->where('ended_at', '<=', $cutoffDate)
            ->delete();
    }

    /**
     * Private Helper Methods
     */
    private function clearPairCaches(Pair $pair): void
    {
        Cache::forget("pair:{$pair->id}");
        Cache::forget("active_pair:user:{$pair->user_id}");
        Cache::forget("active_pair:user:{$pair->partner_id}");
        Cache::forget('active_pairs');
        Cache::forget('pair_statistics');
        Cache::forget('count_active_pairs');
        Cache::forget('count_total_pairs');
    }

    /**
     * Dashboard Statistics Methods
     */
    public function getActiveConversationsCount(): int
    {
        return Cache::remember('pairs:active_count', 300, function () {
            return Pair::where('status', 'active')->count();
        });
    }

    public function getTotalConversationsCount(?int $days = null): int
    {
        return Cache::remember('pairs:total_count:'.($days ?? 'all'), 300, function () use ($days) {
            $query = Pair::query();
            if ($days) {
                $query->where('created_at', '>=', now()->subDays($days));
            }

            return $query->count();
        });
    }

    public function getAverageConversationDuration(): float
    {
        return Cache::remember('pairs:avg_duration', 600, function () {
            // Use database-agnostic approach
            $pairs = Pair::whereNotNull('ended_at')
                ->whereNotNull('started_at')
                ->select(['started_at', 'ended_at'])
                ->get();

            if ($pairs->isEmpty()) {
                return 0.0;
            }

            $totalMinutes = $pairs->sum(function ($pair) {
                return $pair->started_at->diffInMinutes($pair->ended_at);
            });

            return $totalMinutes / $pairs->count();
        });
    }

    public function getActiveConversations(int $limit = 50): Collection
    {
        return Pair::where('status', 'active')
            ->with(['user', 'partner'])
            ->orderBy('started_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getRecentConversations(int $limit = 10): Collection
    {
        return Pair::with(['user', 'partner'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getConversationsCountByDate(Carbon $date): int
    {
        return Pair::whereDate('created_at', $date)->count();
    }

    public function getConversationSuccessRate(): float
    {
        return Cache::remember('pairs:success_rate', 600, function () {
            $total = Pair::count();
            if ($total === 0) {
                return 0;
            }

            // Use database-agnostic approach
            $successful = Pair::where('status', 'ended')
                ->whereNotNull('started_at')
                ->whereNotNull('ended_at')
                ->get()
                ->filter(function ($pair) {
                    return $pair->started_at->diffInMinutes($pair->ended_at) >= 5;
                })
                ->count();

            return ($successful / $total) * 100;
        });
    }

    public function getConversationsByType(): array
    {
        return Cache::remember('pairs:by_type', 600, function () {
            return [
                'active' => Pair::where('status', 'active')->count(),
                'ended' => Pair::where('status', 'ended')->count(),
                'pending' => Pair::where('status', 'pending')->count(),
            ];
        });
    }
}
