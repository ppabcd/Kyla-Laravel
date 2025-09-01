<?php

namespace App\Domain\Repositories;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Balance Transaction Repository Interface
 *
 * Defines contract for balance transaction data access operations
 */
interface BalanceTransactionRepositoryInterface
{
    /**
     * Basic CRUD Operations
     */
    public function findById(int $id): mixed;

    public function create(array $data): mixed;

    public function update(mixed $transaction, array $data): bool;

    public function delete(mixed $transaction): bool;

    /**
     * User Transaction Operations
     */
    public function findByUserId(int $userId): Collection;

    public function findUserTransactionsPaginated(int $userId, int $page = 1, int $perPage = 20): LengthAwarePaginator;

    public function findUserTransactionsByType(int $userId, string $type): Collection;

    public function findUserTransactionsInDateRange(int $userId, string $startDate, string $endDate): Collection;

    /**
     * Transaction Analysis
     */
    public function getTotalCredits(int $userId): float;

    public function getTotalDebits(int $userId): float;

    public function getBalanceHistory(int $userId): Collection;

    public function findLargeTransactions(float $minAmount): Collection;

    /**
     * Statistics Operations
     */
    public function getTotalTransactionAmount(): float;

    public function countTransactionsByType(): array;

    public function getTransactionStatistics(): array;

    public function findTopSpenders(int $limit = 10): Collection;

    /**
     * Reference Operations
     */
    public function findByReference(string $referenceId, string $referenceType): Collection;

    public function linkToReference(mixed $transaction, string $referenceId, string $referenceType): bool;

    /**
     * Audit Operations
     */
    public function findSuspiciousTransactions(): Collection;

    public function findDuplicateTransactions(): Collection;

    public function validateTransactionIntegrity(User $user): bool;
}
