<?php

namespace App\Application\Services;

use App\Domain\Entities\User;
use App\Domain\Entities\Report;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Domain\Repositories\ReportRepositoryInterface;
use App\Domain\Repositories\PairRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Banned Service
 * 
 * Application service responsible for user moderation and banning logic
 * Following Single Responsibility Principle and Clean Architecture
 */
class BannedService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private ReportRepositoryInterface $reportRepository,
        private PairRepositoryInterface $pairRepository
    ) {
    }

    /**
     * Check if a user is banned
     */
    public function isUserBanned(User $user): bool
    {
        return $user->isBanned() || $user->isSoftBanned();
    }

    /**
     * Ban a user permanently
     */
    public function banUser(User $user, string $reason, int $bannedBy, array $evidence = []): bool
    {
        if ($user->isBanned()) {
            Log::warning('Attempted to ban already banned user', ['user_id' => $user->id]);
            return false;
        }

        // End any active pairs
        $this->endUserActivePairs($user, 'User banned');

        // Perform the ban
        $success = $this->userRepository->banUser($user, $reason, $bannedBy);

        if ($success) {
            // Log the ban with evidence
            $this->logBanAction($user, $reason, $bannedBy, $evidence);

            // Clear user caches
            $this->clearUserCaches($user);

            Log::info('User banned successfully', [
                'user_id' => $user->id,
                'banned_by' => $bannedBy,
                'reason' => $reason
            ]);
        }

        return $success;
    }

    /**
     * Unban a user
     */
    public function unbanUser(User $user, int $unbannedBy, string $reason = null): bool
    {
        if (!$user->isBanned()) {
            Log::warning('Attempted to unban non-banned user', ['user_id' => $user->id]);
            return false;
        }

        $success = $this->userRepository->unbanUser($user);

        if ($success) {
            // Log the unban action
            activity()
                ->performedOn($user)
                ->causedBy($unbannedBy)
                ->withProperties([
                    'reason' => $reason ?? 'User unbanned',
                    'previous_ban_reason' => $user->banned_reason
                ])
                ->log('user_unbanned');

            // Clear user caches
            $this->clearUserCaches($user);

            Log::info('User unbanned successfully', [
                'user_id' => $user->id,
                'unbanned_by' => $unbannedBy,
                'reason' => $reason
            ]);
        }

        return $success;
    }

    /**
     * Soft ban a user for a specific duration
     */
    public function softBanUser(User $user, int $minutes, string $reason, int $bannedBy): bool
    {
        if ($user->isBanned()) {
            Log::warning('Attempted to soft ban permanently banned user', ['user_id' => $user->id]);
            return false;
        }

        $success = $this->userRepository->softBanUser($user, $minutes);

        if ($success) {
            // Log the soft ban action
            activity()
                ->performedOn($user)
                ->causedBy($bannedBy)
                ->withProperties([
                    'reason' => $reason,
                    'duration_minutes' => $minutes,
                    'expires_at' => now()->addMinutes($minutes)
                ])
                ->log('user_soft_banned');

            Log::info('User soft banned successfully', [
                'user_id' => $user->id,
                'banned_by' => $bannedBy,
                'reason' => $reason,
                'duration_minutes' => $minutes
            ]);
        }

        return $success;
    }

    /**
     * Auto-ban users based on reports
     */
    public function processAutoBan(User $user): bool
    {
        $recentReports = $this->reportRepository->findRepeatedReports($user->id, 7);
        $reportCount = $recentReports->count();

        // Define auto-ban thresholds
        $softBanThreshold = 3;
        $permanentBanThreshold = 5;

        if ($reportCount >= $permanentBanThreshold) {
            // Auto permanent ban
            return $this->banUser(
                $user,
                "Auto-banned: {$reportCount} reports in 7 days",
                0, // System ban
                ['report_count' => $reportCount, 'reports' => $recentReports->pluck('id')->toArray()]
            );
        } elseif ($reportCount >= $softBanThreshold) {
            // Auto soft ban for escalating duration
            $duration = $this->calculateSoftBanDuration($reportCount);
            return $this->softBanUser(
                $user,
                $duration,
                "Auto-soft banned: {$reportCount} reports in 7 days",
                0 // System ban
            );
        }

        return false;
    }

    /**
     * Get user ban status and history
     */
    public function getUserBanStatus(User $user): array
    {
        $reports = $this->reportRepository->findByReportedUser($user->id);
        $recentReports = $this->reportRepository->findRepeatedReports($user->id, 30);

        return [
            'is_banned' => $user->isBanned(),
            'is_soft_banned' => $user->isSoftBanned(),
            'banned_reason' => $user->banned_reason,
            'banned_at' => $user->banned_at,
            'soft_ban_expires' => $user->soft_ban,
            'total_reports' => $reports->count(),
            'recent_reports_30_days' => $recentReports->count(),
            'ban_risk_level' => $this->calculateBanRiskLevel($user),
            'ban_history' => $this->getBanHistory($user)
        ];
    }

    /**
     * Get ban statistics
     */
    public function getBanStatistics(): array
    {
        return Cache::remember('ban_statistics', 3600, function () {
            $totalUsers = $this->userRepository->findActiveUsers()->count();
            $bannedUsers = $this->userRepository->findBannedUsers();
            $softBannedUsers = $this->userRepository->searchUsers(['is_soft_banned' => true], 1, 1000);

            return [
                'total_banned' => $bannedUsers->count(),
                'total_soft_banned' => $softBannedUsers->count(),
                'ban_rate' => $totalUsers > 0 ? ($bannedUsers->count() / $totalUsers) * 100 : 0,
                'recent_bans_7_days' => $bannedUsers->where('banned_at', '>=', now()->subDays(7))->count(),
                'ban_reasons' => $this->getBanReasonStatistics(),
                'most_reported_users' => $this->reportRepository->findMostReportedUsers(10)
            ];
        });
    }

    /**
     * Review and process pending reports
     */
    public function processPendingReports(): int
    {
        $pendingReports = $this->reportRepository->findPendingReports();
        $processedCount = 0;

        foreach ($pendingReports as $report) {
            if ($this->shouldAutoProcess($report)) {
                $this->processReport($report);
                $processedCount++;
            }
        }

        Log::info('Processed pending reports', ['count' => $processedCount]);

        return $processedCount;
    }

    /**
     * Cleanup expired soft bans
     */
    public function cleanupExpiredSoftBans(): int
    {
        $expiredSoftBans = $this->userRepository->searchUsers(['is_soft_banned' => true], 1, 1000)
            ->getCollection()
            ->filter(function ($user) {
                return !$user->isSoftBanned(); // Soft ban has expired
            });

        $cleanedCount = 0;
        foreach ($expiredSoftBans as $user) {
            if ($this->userRepository->update($user, ['soft_ban' => null])) {
                $cleanedCount++;
            }
        }

        Log::info('Cleaned up expired soft bans', ['count' => $cleanedCount]);

        return $cleanedCount;
    }

    /**
     * Private helper methods
     */
    private function endUserActivePairs(User $user, string $reason): void
    {
        $activePair = $this->pairRepository->findActivePairByUserId($user->id);
        if ($activePair) {
            $this->pairRepository->endPair($activePair, $user->id, $reason);
        }
    }

    private function logBanAction(User $user, string $reason, int $bannedBy, array $evidence): void
    {
        activity()
            ->performedOn($user)
            ->causedBy($bannedBy)
            ->withProperties([
                'reason' => $reason,
                'evidence' => $evidence,
                'user_stats' => [
                    'total_reports' => $this->reportRepository->countReportsAgainstUser($user->id, 365),
                    'total_matches' => $user->total_matches,
                    'account_age_days' => $user->created_at->diffInDays(now())
                ]
            ])
            ->log('user_banned');
    }

    private function calculateSoftBanDuration(int $reportCount): int
    {
        // Escalating soft ban duration based on report count
        return match (true) {
            $reportCount >= 4 => 1440, // 24 hours
            $reportCount >= 3 => 360,  // 6 hours
            default => 60              // 1 hour
        };
    }

    private function calculateBanRiskLevel(User $user): string
    {
        $recentReports = $this->reportRepository->countReportsAgainstUser($user->id, 7);

        return match (true) {
            $recentReports >= 5 => 'critical',
            $recentReports >= 3 => 'high',
            $recentReports >= 2 => 'medium',
            $recentReports >= 1 => 'low',
            default => 'none'
        };
    }

    private function getBanHistory(User $user): array
    {
        // This would typically come from an audit log table
        // For now, return basic information
        return [
            'total_bans' => $user->banned_at ? 1 : 0,
            'current_ban' => $user->isBanned() ? [
                'reason' => $user->banned_reason,
                'banned_at' => $user->banned_at
            ] : null,
            'soft_ban_count' => 0 // Would need separate tracking
        ];
    }

    private function getBanReasonStatistics(): array
    {
        return Cache::remember('ban_reason_statistics', 3600, function () {
            return $this->userRepository->findBannedUsers()
                ->groupBy('banned_reason')
                ->map(function ($group) {
                    return $group->count();
                })
                ->toArray();
        });
    }

    private function shouldAutoProcess(Report $report): bool
    {
        // Simple auto-processing rules
        $reportedUser = $report->reportedUser;
        if (!$reportedUser) {
            return false;
        }

        $recentReports = $this->reportRepository->countReportsAgainstUser($reportedUser->id, 7);

        // Auto-process if user has multiple reports in a short time
        return $recentReports >= 3;
    }

    private function processReport(Report $report): void
    {
        $reportedUser = $report->reportedUser;
        if (!$reportedUser) {
            return;
        }

        // Check if auto-ban should be triggered
        $autoBanned = $this->processAutoBan($reportedUser);

        // Mark report as reviewed
        $action = $autoBanned ? 'Auto-banned user' : 'Auto-reviewed';
        $this->reportRepository->markAsReviewed($report, 0, $action, 'Processed automatically');
    }

    private function clearUserCaches(User $user): void
    {
        Cache::forget("user:{$user->id}");
        Cache::forget("user:telegram:{$user->telegram_id}");
        Cache::forget('ban_statistics');
    }
}
