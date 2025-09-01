<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\Report;
use App\Domain\Entities\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Report Repository Interface
 *
 * Defines contract for report data access operations
 */
interface ReportRepositoryInterface
{
    /**
     * Basic CRUD Operations
     */
    public function findById(int $id): ?Report;

    public function create(array $data): Report;

    public function update(Report $report, array $data): bool;

    public function delete(Report $report): bool;

    /**
     * Report Query Operations
     */
    public function findByReporter(int $reporterUserId): Collection;

    public function findByReportedUser(int $reportedUserId): Collection;

    public function findByStatus(string $status): Collection;

    public function findByReason(string $reason): Collection;

    /**
     * Admin Operations
     */
    public function findPendingReports(): Collection;

    public function findReportsForReview(): Collection;

    public function findReportsPaginated(int $page = 1, int $perPage = 20): LengthAwarePaginator;

    public function assignReviewer(Report $report, int $reviewerId): bool;

    /**
     * Report Management
     */
    public function markAsReviewed(Report $report, int $reviewerId, string $actionTaken, ?string $notes = null): bool;

    public function resolve(Report $report): bool;

    public function dismiss(Report $report, ?string $reason = null): bool;

    /**
     * Statistics Operations
     */
    public function countReportsByStatus(): array;

    public function countReportsByReason(): array;

    public function findMostReportedUsers(int $limit = 10): Collection;

    public function findTopReporters(int $limit = 10): Collection;

    /**
     * User Safety Operations
     */
    public function findRepeatedReports(int $reportedUserId, int $days = 30): Collection;

    public function countReportsAgainstUser(int $reportedUserId, int $days = 30): int;

    public function findSuspiciousReportPatterns(): Collection;

    /**
     * Cleanup Operations
     */
    public function deleteOldResolvedReports(int $daysOld): int;

    public function archiveOldReports(int $daysOld): int;
}
