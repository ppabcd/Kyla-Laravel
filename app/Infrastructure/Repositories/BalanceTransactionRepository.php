<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\BalanceTransaction;
use App\Domain\Entities\User;
use App\Domain\Repositories\BalanceTransactionRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Balance Transaction Repository Implementation
 *
 * Infrastructure layer implementation of BalanceTransactionRepositoryInterface
 */
class BalanceTransactionRepository implements BalanceTransactionRepositoryInterface
{
    /**
     * Basic CRUD Operations
     */
    public function findById(int $id): ?BalanceTransaction
    {
        return BalanceTransaction::with('user')->find($id);
    }

    public function create(array $data): BalanceTransaction
    {
        return BalanceTransaction::create($data);
    }

    public function update(BalanceTransaction $transaction, array $data): bool
    {
        return $transaction->update($data);
    }

    public function delete(BalanceTransaction $transaction): bool
    {
        return $transaction->delete();
    }

    /**
     * User Transaction Operations
     */
    public function findByUserId(int $userId): Collection
    {
        return BalanceTransaction::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findUserTransactionsPaginated(int $userId, int $page = 1, int $perPage = 20): LengthAwarePaginator
    {
        return BalanceTransaction::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function findUserTransactionsByType(int $userId, string $type): Collection
    {
        return BalanceTransaction::where('user_id', $userId)
            ->where('type', $type)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findUserTransactionsInDateRange(int $userId, string $startDate, string $endDate): Collection
    {
        return BalanceTransaction::where('user_id', $userId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Transaction Analysis
     */
    public function getTotalCredits(int $userId): float
    {
        return Cache::remember("user_total_credits:{$userId}", 3600, function () use ($userId) {
            return BalanceTransaction::where('user_id', $userId)
                ->where('type', 'credit')
                ->sum('amount');
        });
    }

    public function getTotalDebits(int $userId): float
    {
        return Cache::remember("user_total_debits:{$userId}", 3600, function () use ($userId) {
            return BalanceTransaction::where('user_id', $userId)
                ->where('type', 'debit')
                ->sum('amount');
        });
    }

    public function getBalanceHistory(int $userId): Collection
    {
        return BalanceTransaction::where('user_id', $userId)
            ->select(['id', 'type', 'amount', 'current_balance', 'description', 'created_at'])
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function findLargeTransactions(float $minAmount): Collection
    {
        return BalanceTransaction::where('amount', '>=', $minAmount)
            ->with('user')
            ->orderBy('amount', 'desc')
            ->get();
    }

    /**
     * Statistics Operations
     */
    public function getTotalTransactionAmount(): float
    {
        return Cache::remember('total_transaction_amount', 3600, function () {
            return BalanceTransaction::sum('amount');
        });
    }

    public function countTransactionsByType(): array
    {
        return Cache::remember('transaction_count_by_type', 3600, function () {
            return BalanceTransaction::select('type', DB::raw('count(*) as count'))
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray();
        });
    }

    public function getTransactionStatistics(): array
    {
        return Cache::remember('transaction_statistics', 3600, function () {
            $stats = DB::table('balance_transactions')
                ->selectRaw('
                    type,
                    COUNT(*) as count,
                    SUM(amount) as total_amount,
                    AVG(amount) as avg_amount,
                    MIN(amount) as min_amount,
                    MAX(amount) as max_amount
                ')
                ->groupBy('type')
                ->get()
                ->keyBy('type');

            return [
                'credits' => [
                    'count' => $stats->get('credit')->count ?? 0,
                    'total_amount' => $stats->get('credit')->total_amount ?? 0,
                    'avg_amount' => $stats->get('credit')->avg_amount ?? 0,
                    'min_amount' => $stats->get('credit')->min_amount ?? 0,
                    'max_amount' => $stats->get('credit')->max_amount ?? 0,
                ],
                'debits' => [
                    'count' => $stats->get('debit')->count ?? 0,
                    'total_amount' => $stats->get('debit')->total_amount ?? 0,
                    'avg_amount' => $stats->get('debit')->avg_amount ?? 0,
                    'min_amount' => $stats->get('debit')->min_amount ?? 0,
                    'max_amount' => $stats->get('debit')->max_amount ?? 0,
                ],
                'total_transactions' => BalanceTransaction::count(),
                'total_amount' => $this->getTotalTransactionAmount(),
            ];
        });
    }

    public function findTopSpenders(int $limit = 10): Collection
    {
        return Cache::remember("top_spenders:{$limit}", 3600, function () use ($limit) {
            return BalanceTransaction::select('user_id', DB::raw('SUM(amount) as total_spent'))
                ->where('type', 'debit')
                ->with('user')
                ->groupBy('user_id')
                ->orderByDesc('total_spent')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Reference Operations
     */
    public function findByReference(string $referenceId, string $referenceType): Collection
    {
        return BalanceTransaction::where('reference_id', $referenceId)
            ->where('reference_type', $referenceType)
            ->with('user')
            ->get();
    }

    public function linkToReference(BalanceTransaction $transaction, string $referenceId, string $referenceType): bool
    {
        return $this->update($transaction, [
            'reference_id' => $referenceId,
            'reference_type' => $referenceType,
        ]);
    }

    /**
     * Audit Operations
     */
    public function findSuspiciousTransactions(): Collection
    {
        return BalanceTransaction::where(function ($query) {
            // Large transactions
            $query->where('amount', '>', 1000)
                // Or multiple transactions in short time
                ->orWhereIn('user_id', function ($subQuery) {
                    $subQuery->select('user_id')
                        ->from('balance_transactions')
                        ->where('created_at', '>=', now()->subHour())
                        ->groupBy('user_id')
                        ->havingRaw('COUNT(*) > 10');
                });
        })
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findDuplicateTransactions(): Collection
    {
        return BalanceTransaction::select('*')
            ->whereIn(
                DB::raw('(user_id, amount, description, created_at)'),
                function ($query) {
                    $query->select('user_id', 'amount', 'description', 'created_at')
                        ->from('balance_transactions')
                        ->groupBy('user_id', 'amount', 'description', 'created_at')
                        ->havingRaw('COUNT(*) > 1');
                }
            )
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function validateTransactionIntegrity(User $user): bool
    {
        $transactions = $this->findByUserId($user->id);

        if ($transactions->isEmpty()) {
            return $user->balance == 0;
        }

        $calculatedBalance = 0;
        foreach ($transactions->sortBy('created_at') as $transaction) {
            if ($transaction->type === 'credit') {
                $calculatedBalance += $transaction->amount;
            } else {
                $calculatedBalance -= $transaction->amount;
            }

            // Check if the recorded current_balance matches our calculation
            if (abs($transaction->current_balance - $calculatedBalance) > 0.01) {
                return false;
            }
        }

        // Check if final balance matches user's current balance
        return abs($user->balance - $calculatedBalance) < 0.01;
    }

    /**
     * Dashboard Statistics Methods
     */
    public function getTotalBalance(): float
    {
        return Cache::remember('transactions:total_balance', 300, function () {
            // Calculate total balance from all users
            return DB::table('users')->sum('balance') ?? 0;
        });
    }

    public function getTotalRevenue(?int $days = null): float
    {
        return Cache::remember('transactions:revenue:'.($days ?? 'all'), 600, function () use ($days) {
            $query = BalanceTransaction::where('type', 'credit')
                ->whereIn('description', ['Premium Purchase', 'Top Up', 'Gift Purchase']);

            if ($days) {
                $query->where('created_at', '>=', now()->subDays($days));
            }

            return $query->sum('amount');
        });
    }

    public function getRevenueByPeriod(string $period = 'week'): float
    {
        $days = match ($period) {
            'day' => 1,
            'week' => 7,
            'month' => 30,
            'year' => 365,
            default => 7
        };

        return $this->getTotalRevenue($days);
    }

    public function getTransactionsCount(?int $days = null): int
    {
        return Cache::remember('transactions:count:'.($days ?? 'all'), 300, function () use ($days) {
            $query = BalanceTransaction::query();
            if ($days) {
                $query->where('created_at', '>=', now()->subDays($days));
            }

            return $query->count();
        });
    }

    public function getRevenueByDate(Carbon $date): float
    {
        return BalanceTransaction::where('type', 'credit')
            ->whereIn('description', ['Premium Purchase', 'Top Up', 'Gift Purchase'])
            ->whereDate('created_at', $date)
            ->sum('amount');
    }

    public function getTransactionCountByDate(Carbon $date): int
    {
        return BalanceTransaction::whereDate('created_at', $date)->count();
    }

    public function getTransactionsByType(): array
    {
        return Cache::remember('transactions:by_type', 600, function () {
            return BalanceTransaction::selectRaw('type, COUNT(*) as count, SUM(amount) as total')
                ->groupBy('type')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [
                        $item->type => [
                            'count' => $item->count,
                            'total' => $item->total,
                        ],
                    ];
                })
                ->toArray();
        });
    }

    public function getTopSpendersForDashboard(int $limit = 10): Collection
    {
        return Cache::remember("transactions:top_spenders:{$limit}", 600, function () use ($limit) {
            return BalanceTransaction::selectRaw('user_id, SUM(amount) as total_spent, COUNT(*) as transaction_count')
                ->where('type', 'debit')
                ->with('user:id,first_name,last_name,username,telegram_id')
                ->groupBy('user_id')
                ->orderBy('total_spent', 'desc')
                ->limit($limit)
                ->get();
        });
    }

    public function getRecentTransactions(int $limit = 20): Collection
    {
        return BalanceTransaction::with('user:id,first_name,last_name,username')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getMonthlyRevenue(): array
    {
        return Cache::remember('transactions:monthly_revenue', 3600, function () {
            return BalanceTransaction::selectRaw('
                    YEAR(created_at) as year,
                    MONTH(created_at) as month,
                    SUM(CASE WHEN type = "credit" THEN amount ELSE 0 END) as revenue
                ')
                ->where('created_at', '>=', now()->subYear())
                ->whereIn('description', ['Premium Purchase', 'Top Up', 'Gift Purchase'])
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->get()
                ->mapWithKeys(function ($item) {
                    return ["{$item->year}-{$item->month}" => $item->revenue];
                })
                ->toArray();
        });
    }

    public function getAverageTransactionAmount(): float
    {
        return Cache::remember('transactions:avg_amount', 600, function () {
            return BalanceTransaction::avg('amount') ?? 0;
        });
    }

    public function getTransactionTrend(int $days = 30): array
    {
        return Cache::remember("transactions:trend:{$days}", 600, function () use ($days) {
            return BalanceTransaction::selectRaw('
                    DATE(created_at) as date,
                    COUNT(*) as count,
                    SUM(amount) as total
                ')
                ->where('created_at', '>=', now()->subDays($days))
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [
                        $item->date => [
                            'count' => $item->count,
                            'total' => $item->total,
                        ],
                    ];
                })
                ->toArray();
        });
    }
}
