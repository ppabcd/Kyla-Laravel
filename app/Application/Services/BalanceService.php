<?php

namespace App\Application\Services;

use App\Domain\Entities\User;
use App\Domain\Entities\BalanceTransaction;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Domain\Repositories\BalanceTransactionRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Balance Service
 * 
 * Application service responsible for user balance and transaction management
 * Following Single Responsibility Principle and Clean Architecture
 */
class BalanceService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private BalanceTransactionRepositoryInterface $transactionRepository
    ) {
    }

    /**
     * Add balance to user account
     */
    public function addBalance(User $user, float $amount, string $description = 'Balance credit', string $referenceId = null, string $referenceType = null): bool
    {
        if ($amount <= 0) {
            Log::warning('Attempted to add negative or zero balance', [
                'user_id' => $user->id,
                'amount' => $amount
            ]);
            return false;
        }

        return DB::transaction(function () use ($user, $amount, $description, $referenceId, $referenceType) {
            $previousBalance = $user->balance;
            $newBalance = $previousBalance + $amount;

            // Update user balance
            $updated = $this->userRepository->update($user, ['balance' => $newBalance]);

            if ($updated) {
                // Create transaction record
                $transaction = $this->transactionRepository->create([
                    'user_id' => $user->id,
                    'type' => 'credit',
                    'amount' => $amount,
                    'previous_balance' => $previousBalance,
                    'current_balance' => $newBalance,
                    'description' => $description,
                    'reference_id' => $referenceId,
                    'reference_type' => $referenceType
                ]);

                Log::info('Balance added successfully', [
                    'user_id' => $user->id,
                    'amount' => $amount,
                    'new_balance' => $newBalance,
                    'transaction_id' => $transaction->id
                ]);

                // Clear user cache
                $this->clearUserBalanceCache($user);

                return true;
            }

            return false;
        });
    }

    /**
     * Deduct balance from user account
     */
    public function deductBalance(User $user, float $amount, string $description = 'Balance debit', string $referenceId = null, string $referenceType = null): bool
    {
        if ($amount <= 0) {
            Log::warning('Attempted to deduct negative or zero balance', [
                'user_id' => $user->id,
                'amount' => $amount
            ]);
            return false;
        }

        if ($user->balance < $amount) {
            Log::warning('Insufficient balance for deduction', [
                'user_id' => $user->id,
                'current_balance' => $user->balance,
                'requested_amount' => $amount
            ]);
            return false;
        }

        return DB::transaction(function () use ($user, $amount, $description, $referenceId, $referenceType) {
            $previousBalance = $user->balance;
            $newBalance = $previousBalance - $amount;

            // Update user balance
            $updated = $this->userRepository->update($user, ['balance' => $newBalance]);

            if ($updated) {
                // Create transaction record
                $transaction = $this->transactionRepository->create([
                    'user_id' => $user->id,
                    'type' => 'debit',
                    'amount' => $amount,
                    'previous_balance' => $previousBalance,
                    'current_balance' => $newBalance,
                    'description' => $description,
                    'reference_id' => $referenceId,
                    'reference_type' => $referenceType
                ]);

                Log::info('Balance deducted successfully', [
                    'user_id' => $user->id,
                    'amount' => $amount,
                    'new_balance' => $newBalance,
                    'transaction_id' => $transaction->id
                ]);

                // Clear user cache
                $this->clearUserBalanceCache($user);

                return true;
            }

            return false;
        });
    }

    /**
     * Transfer balance between users
     */
    public function transferBalance(User $fromUser, User $toUser, float $amount, string $description = 'Balance transfer'): bool
    {
        if ($amount <= 0) {
            Log::warning('Attempted to transfer negative or zero balance', [
                'from_user_id' => $fromUser->id,
                'to_user_id' => $toUser->id,
                'amount' => $amount
            ]);
            return false;
        }

        if ($fromUser->balance < $amount) {
            Log::warning('Insufficient balance for transfer', [
                'from_user_id' => $fromUser->id,
                'current_balance' => $fromUser->balance,
                'transfer_amount' => $amount
            ]);
            return false;
        }

        return DB::transaction(function () use ($fromUser, $toUser, $amount, $description) {
            $transferId = 'TRX_' . time() . '_' . $fromUser->id . '_' . $toUser->id;

            // Deduct from sender
            $deducted = $this->deductBalance(
                $fromUser,
                $amount,
                "Transfer to user {$toUser->id}: {$description}",
                $transferId,
                'transfer_out'
            );

            if (!$deducted) {
                return false;
            }

            // Add to receiver
            $added = $this->addBalance(
                $toUser,
                $amount,
                "Transfer from user {$fromUser->id}: {$description}",
                $transferId,
                'transfer_in'
            );

            if (!$added) {
                // Rollback by adding back to sender
                $this->addBalance(
                    $fromUser,
                    $amount,
                    "Transfer rollback: {$description}",
                    $transferId,
                    'transfer_rollback'
                );
                return false;
            }

            Log::info('Balance transfer completed', [
                'from_user_id' => $fromUser->id,
                'to_user_id' => $toUser->id,
                'amount' => $amount,
                'transfer_id' => $transferId
            ]);

            return true;
        });
    }

    /**
     * Get user balance history
     */
    public function getBalanceHistory(User $user, int $limit = 50): array
    {
        $transactions = $this->transactionRepository->findByUserId($user->id)->take($limit);

        return $transactions->map(function ($transaction) {
            return [
                'id' => $transaction->id,
                'type' => $transaction->type,
                'amount' => $transaction->getFormattedAmount(),
                'description' => $transaction->description,
                'balance_after' => $transaction->current_balance,
                'created_at' => $transaction->created_at,
                'reference' => [
                    'id' => $transaction->reference_id,
                    'type' => $transaction->reference_type
                ]
            ];
        })->toArray();
    }

    /**
     * Get balance statistics for user
     */
    public function getUserBalanceStats(User $user): array
    {
        return Cache::remember("user_balance_stats:{$user->id}", 3600, function () use ($user) {
            $totalCredits = $this->transactionRepository->getTotalCredits($user->id);
            $totalDebits = $this->transactionRepository->getTotalDebits($user->id);
            $transactions = $this->transactionRepository->findByUserId($user->id);

            return [
                'current_balance' => $user->balance,
                'total_credits' => $totalCredits,
                'total_debits' => $totalDebits,
                'total_transactions' => $transactions->count(),
                'average_transaction' => $transactions->count() > 0
                    ? $transactions->avg('amount')
                    : 0,
                'last_transaction' => $transactions->first()?->created_at,
                'monthly_summary' => $this->getMonthlyTransactionSummary($user)
            ];
        });
    }

    /**
     * Validate transaction integrity for user
     */
    public function validateUserBalanceIntegrity(User $user): array
    {
        $isValid = $this->transactionRepository->validateTransactionIntegrity($user);
        $transactions = $this->transactionRepository->findByUserId($user->id);

        $calculatedBalance = 0;
        $discrepancies = [];

        foreach ($transactions->sortBy('created_at') as $transaction) {
            if ($transaction->type === 'credit') {
                $calculatedBalance += $transaction->amount;
            } else {
                $calculatedBalance -= $transaction->amount;
            }

            if (abs($transaction->current_balance - $calculatedBalance) > 0.01) {
                $discrepancies[] = [
                    'transaction_id' => $transaction->id,
                    'expected_balance' => $calculatedBalance,
                    'recorded_balance' => $transaction->current_balance,
                    'difference' => $transaction->current_balance - $calculatedBalance
                ];
            }
        }

        return [
            'is_valid' => $isValid,
            'calculated_balance' => $calculatedBalance,
            'recorded_balance' => $user->balance,
            'difference' => $user->balance - $calculatedBalance,
            'discrepancies' => $discrepancies,
            'total_transactions' => $transactions->count()
        ];
    }

    /**
     * Process daily balance rewards
     */
    public function processDailyReward(User $user): bool
    {
        $rewardAmount = $this->calculateDailyReward($user);

        if ($rewardAmount <= 0) {
            return false;
        }

        return $this->addBalance(
            $user,
            $rewardAmount,
            'Daily login reward',
            'daily_reward_' . now()->format('Y-m-d'),
            'daily_reward'
        );
    }

    /**
     * Get transaction pretty print format
     */
    public function getPrettyPrintTransaction(array $transactionData): string
    {
        $userId = $transactionData['userId'] ?? 'Unknown';
        $currentBalance = $transactionData['currentBalance'] ?? 0;
        $previousBalance = $transactionData['previousBalance'] ?? 0;
        $amount = $transactionData['amount'] ?? 0;
        $description = $transactionData['description'] ?? 'No description';

        return "👤 User: *{$userId}*\n" .
            "👝 Previous Balance: {$previousBalance}\n" .
            "💼 Current Balance: *{$currentBalance}*\n" .
            "💰 Amount: *{$amount}*\n" .
            "📝 Description: *{$description}*";
    }

    /**
     * Private helper methods
     */
    private function getMonthlyTransactionSummary(User $user): array
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $monthlyTransactions = $this->transactionRepository->findUserTransactionsInDateRange(
            $user->id,
            $startOfMonth->toDateString(),
            $endOfMonth->toDateString()
        );

        $credits = $monthlyTransactions->where('type', 'credit');
        $debits = $monthlyTransactions->where('type', 'debit');

        return [
            'total_credits' => $credits->sum('amount'),
            'total_debits' => $debits->sum('amount'),
            'credit_count' => $credits->count(),
            'debit_count' => $debits->count(),
            'net_change' => $credits->sum('amount') - $debits->sum('amount')
        ];
    }

    private function calculateDailyReward(User $user): float
    {
        // Base reward
        $baseReward = 10.0;

        // Premium bonus
        if ($user->isPremium()) {
            $baseReward *= 2;
        }

        // Activity bonus
        if ($user->isActive()) {
            $baseReward += 5.0;
        }

        // Rating bonus
        $averageRating = $user->getAverageRating();
        if ($averageRating >= 4.5) {
            $baseReward += 10.0;
        } elseif ($averageRating >= 4.0) {
            $baseReward += 5.0;
        }

        return $baseReward;
    }

    private function clearUserBalanceCache(User $user): void
    {
        Cache::forget("user_balance_stats:{$user->id}");
        Cache::forget("user:{$user->id}");
    }
}
