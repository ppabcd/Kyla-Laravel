<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\Report;
use App\Domain\Entities\User;
use App\Domain\Repositories\ReportRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/**
 * Report Repository Implementation
 * 
 * Infrastructure layer implementation of ReportRepositoryInterface
 */
class ReportRepository implements ReportRepositoryInterface
{
    /**
     * Basic CRUD Operations
     */
    public function findById(int $id): ?Report
    {
        return Report::with(['reporter', 'reportedUser', 'reviewer'])->find($id);
    }

    public function create(array $data): Report
    {
        return Report::create($data);
    }

    public function update(Report $report, array $data): bool
    {
        return $report->update($data);
    }

    public function delete(Report $report): bool
    {
        return $report->delete();
    }

    /**
     * Report Query Operations
     */
    public function findByReporter(int $reporterUserId): Collection
    {
        return Report::where('reporter_user_id', $reporterUserId)
            ->with(['reportedUser', 'reviewer'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findByReportedUser(int $reportedUserId): Collection
    {
        return Report::where('reported_user_id', $reportedUserId)
            ->with(['reporter', 'reviewer'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findByStatus(string $status): Collection
    {
        return Report::where('status', $status)
            ->with(['reporter', 'reportedUser', 'reviewer'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findByReason(string $reason): Collection
    {
        return Report::where('reason', $reason)
            ->with(['reporter', 'reportedUser', 'reviewer'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Admin Operations
     */
    public function findPendingReports(): Collection
    {
        return Cache::remember('pending_reports', 300, function () {
            return Report::where('status', 'pending')
                ->with(['reporter', 'reportedUser'])
                ->orderBy('created_at', 'asc')
                ->get();
        });
    }

    public function findReportsForReview(): Collection
    {
        return Report::whereIn('status', ['pending', 'reviewed'])
            ->with(['reporter', 'reportedUser', 'reviewer'])
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function findReportsPaginated(int $page = 1, int $perPage = 20): LengthAwarePaginator
    {
        return Report::with(['reporter', 'reportedUser', 'reviewer'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function assignReviewer(Report $report, int $reviewerId): bool
    {
        return $this->update($report, [
            'reviewed_by' => $reviewerId,
            'status' => 'under_review'
        ]);
    }

    /**
     * Report Management
     */
    public function markAsReviewed(Report $report, int $reviewerId, string $actionTaken, ?string $notes = null): bool
    {
        return $this->update($report, [
            'status' => 'reviewed',
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'action_taken' => $actionTaken,
            'admin_notes' => $notes
        ]);
    }

    public function resolve(Report $report): bool
    {
        return $this->update($report, [
            'status' => 'resolved'
        ]);
    }

    public function dismiss(Report $report, ?string $reason = null): bool
    {
        return $this->update($report, [
            'status' => 'dismissed',
            'admin_notes' => $reason
        ]);
    }

    /**
     * Statistics Operations
     */
    public function countReportsByStatus(): array
    {
        return Cache::remember('report_count_by_status', 3600, function () {
            return Report::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();
        });
    }

    public function countReportsByReason(): array
    {
        return Cache::remember('report_count_by_reason', 3600, function () {
            return Report::select('reason', DB::raw('count(*) as count'))
                ->groupBy('reason')
                ->pluck('count', 'reason')
                ->toArray();
        });
    }

    public function findMostReportedUsers(int $limit = 10): Collection
    {
        return Cache::remember("most_reported_users:{$limit}", 3600, function () use ($limit) {
            return Report::select('reported_user_id', DB::raw('count(*) as report_count'))
                ->with('reportedUser')
                ->groupBy('reported_user_id')
                ->orderByDesc('report_count')
                ->limit($limit)
                ->get();
        });
    }

    public function findTopReporters(int $limit = 10): Collection
    {
        return Cache::remember("top_reporters:{$limit}", 3600, function () use ($limit) {
            return Report::select('reporter_user_id', DB::raw('count(*) as report_count'))
                ->with('reporter')
                ->groupBy('reporter_user_id')
                ->orderByDesc('report_count')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * User Safety Operations
     */
    public function findRepeatedReports(int $reportedUserId, int $days = 30): Collection
    {
        return Report::where('reported_user_id', $reportedUserId)
            ->where('created_at', '>=', now()->subDays($days))
            ->with(['reporter'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function countReportsAgainstUser(int $reportedUserId, int $days = 30): int
    {
        return Cache::remember("report_count_user:{$reportedUserId}:{$days}", 1800, function () use ($reportedUserId, $days) {
            return Report::where('reported_user_id', $reportedUserId)
                ->where('created_at', '>=', now()->subDays($days))
                ->count();
        });
    }

    public function findSuspiciousReportPatterns(): Collection
    {
        // Find users who report the same user multiple times
        $multipleReports = Report::select('reporter_user_id', 'reported_user_id', DB::raw('count(*) as count'))
            ->groupBy('reporter_user_id', 'reported_user_id')
            ->having('count', '>', 1)
            ->get();

        // Find users who make too many reports in a short time
        $frequentReporters = Report::select('reporter_user_id', DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('reporter_user_id')
            ->having('count', '>', 10)
            ->with('reporter')
            ->get();

        return $multipleReports->merge($frequentReporters);
    }

    /**
     * Cleanup Operations
     */
    public function deleteOldResolvedReports(int $daysOld): int
    {
        $cutoffDate = now()->subDays($daysOld);

        return Report::where('status', 'resolved')
            ->where('reviewed_at', '<=', $cutoffDate)
            ->delete();
    }

    public function archiveOldReports(int $daysOld): int
    {
        $cutoffDate = now()->subDays($daysOld);

        return Report::where('created_at', '<=', $cutoffDate)
            ->update(['status' => 'archived']);
    }

    /**
     * Dashboard Statistics Methods
     */
    public function getPendingReportsCount(): int
    {
        return Cache::remember('reports:pending_count', 300, function () {
            return Report::where('status', 'pending')->count();
        });
    }

    public function getTotalReportsCount(?int $days = null): int
    {
        return Cache::remember("reports:total_count:" . ($days ?? 'all'), 300, function () use ($days) {
            $query = Report::query();
            if ($days) {
                $query->where('created_at', '>=', now()->subDays($days));
            }
            return $query->count();
        });
    }

    public function getResolvedReportsCount(?int $days = null): int
    {
        return Cache::remember("reports:resolved_count:" . ($days ?? 'all'), 300, function () use ($days) {
            $query = Report::where('status', 'resolved');
            if ($days) {
                $query->where('created_at', '>=', now()->subDays($days));
            }
            return $query->count();
        });
    }

    public function getRecentReports(int $limit = 10): Collection
    {
        return Cache::remember("reports:recent:{$limit}", 300, function () use ($limit) {
            return Report::with(['reporter:id,first_name,last_name,username', 'reportedUser:id,first_name,last_name,username'])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();
        });
    }

    public function getAutoBansCount(int $days = 7): int
    {
        return Cache::remember("reports:auto_bans:{$days}", 300, function () use ($days) {
            // Count reports that resulted in automatic bans
            return Report::where('action_taken', 'auto_ban')
                ->where('created_at', '>=', now()->subDays($days))
                ->count();
        });
    }

    public function getReportTypeDistribution(): array
    {
        return Cache::remember('reports:type_distribution', 600, function () {
            return Report::selectRaw('reason, COUNT(*) as count')
                ->groupBy('reason')
                ->pluck('count', 'reason')
                ->toArray();
        });
    }
}
