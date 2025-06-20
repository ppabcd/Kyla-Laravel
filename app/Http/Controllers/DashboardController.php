<?php

namespace App\Http\Controllers;

use App\Application\Services\UserService;
use App\Application\Services\MatchingService;
use App\Application\Services\BalanceService;
use App\Application\Services\BannedService;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Domain\Repositories\PairRepositoryInterface;
use App\Domain\Repositories\BalanceTransactionRepositoryInterface;
use App\Domain\Repositories\ReportRepositoryInterface;
use App\Domain\Repositories\WordFilterRepositoryInterface;
use App\Domain\Entities\WordFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class DashboardController extends Controller
{
    private UserService $userService;
    private MatchingService $matchingService;
    private BalanceService $balanceService;
    private BannedService $bannedService;
    private UserRepositoryInterface $userRepository;
    private PairRepositoryInterface $pairRepository;
    private BalanceTransactionRepositoryInterface $balanceTransactionRepository;
    private ReportRepositoryInterface $reportRepository;
    private WordFilterRepositoryInterface $wordFilterRepository;

    public function __construct(
        UserService $userService,
        MatchingService $matchingService,
        BalanceService $balanceService,
        BannedService $bannedService,
        UserRepositoryInterface $userRepository,
        PairRepositoryInterface $pairRepository,
        BalanceTransactionRepositoryInterface $balanceTransactionRepository,
        ReportRepositoryInterface $reportRepository,
        WordFilterRepositoryInterface $wordFilterRepository
    ) {
        $this->userService = $userService;
        $this->matchingService = $matchingService;
        $this->balanceService = $balanceService;
        $this->bannedService = $bannedService;
        $this->userRepository = $userRepository;
        $this->pairRepository = $pairRepository;
        $this->balanceTransactionRepository = $balanceTransactionRepository;
        $this->reportRepository = $reportRepository;
        $this->wordFilterRepository = $wordFilterRepository;
    }

    /**
     * Show the main dashboard
     */
    public function index()
    {
        $stats = $this->getDashboardStats();
        $charts = $this->getChartData();
        $recentActivity = $this->getRecentActivity();

        return view('dashboard.index', compact('stats', 'charts', 'recentActivity'));
    }

    /**
     * Show users management page
     */
    public function users(Request $request)
    {
        $filters = $request->only(['search', 'status', 'gender', 'premium']);
        $users = $this->userRepository->getFilteredUsers($filters, 20);
        $userStats = $this->getUserStats();

        return view('dashboard.users', compact('users', 'userStats', 'filters'));
    }

    /**
     * Show conversations analytics
     */
    public function conversations()
    {
        $conversationStats = $this->getConversationStats();
        $activeConversations = $this->pairRepository->getActiveConversations(50);

        return view('dashboard.conversations', compact('conversationStats', 'activeConversations'));
    }

    /**
     * Show financial analytics
     */
    public function finances()
    {
        $financialStats = $this->getFinancialStats();
        $recentTransactions = $this->balanceTransactionRepository->getRecentTransactions(100);
        $revenueChart = $this->getRevenueChartData();

        return view('dashboard.finances', compact('financialStats', 'recentTransactions', 'revenueChart'));
    }

    /**
     * Show moderation panel
     */
    public function moderation(Request $request)
    {
        $moderationStats = $this->getModerationStats();
        $recentReports = $this->reportRepository->getRecentReports(50);
        $bannedUsers = $this->userRepository->getBannedUsers(50);

        // Word filter data
        $wordFilterStats = $this->wordFilterRepository->getStatistics();
        $wordFilters = $this->wordFilterRepository->getFilteredWords(
            $request->only(['search', 'type', 'ai_check']),
            15
        );
        $recentWords = $this->wordFilterRepository->getRecentWords(10);

        return view('dashboard.moderation', compact(
            'moderationStats',
            'recentReports',
            'bannedUsers',
            'wordFilterStats',
            'wordFilters',
            'recentWords'
        ));
    }

    /**
     * Show system analytics
     */
    public function analytics()
    {
        $analyticsData = $this->getAnalyticsData();

        return view('dashboard.analytics', compact('analyticsData'));
    }

    /**
     * API endpoint for real-time stats
     */
    public function apiStats()
    {
        return response()->json($this->getDashboardStats());
    }

    /**
     * Get main dashboard statistics
     */
    private function getDashboardStats(): array
    {
        return Cache::remember('dashboard_stats', 300, function () {
            return [
                'total_users' => $this->userRepository->getTotalUsers(),
                'active_users_today' => $this->userRepository->getActiveUsersCount(1),
                'active_users_week' => $this->userRepository->getActiveUsersCount(7),
                'new_users_today' => $this->userRepository->getNewUsersCount(1),
                'new_users_week' => $this->userRepository->getNewUsersCount(7),
                'active_conversations' => $this->pairRepository->getActiveConversationsCount(),
                'total_conversations_today' => $this->pairRepository->getTotalConversationsCount(1),
                'avg_conversation_duration' => $this->pairRepository->getAverageConversationDuration(),
                'queue_length' => $this->userRepository->getSearchQueueLength(),
                'banned_users' => $this->userRepository->getBannedUsersCount(),
                'total_balance' => $this->balanceTransactionRepository->getTotalBalance(),
                'revenue_today' => $this->balanceTransactionRepository->getRevenueByPeriod(1),
                'revenue_week' => $this->balanceTransactionRepository->getRevenueByPeriod(7),
                'pending_reports' => $this->reportRepository->getPendingReportsCount(),
                'system_health' => $this->getSystemHealth(),
            ];
        });
    }

    /**
     * Get chart data for dashboard
     */
    private function getChartData(): array
    {
        return Cache::remember('dashboard_charts', 600, function () {
            $last30Days = collect(range(0, 29))->map(function ($day) {
                $date = Carbon::now()->subDays($day);
                return [
                    'date' => $date->format('Y-m-d'),
                    'users' => $this->userRepository->getNewUsersCountByDate($date),
                    'conversations' => $this->pairRepository->getConversationsCountByDate($date),
                    'revenue' => $this->balanceTransactionRepository->getRevenueByDate($date),
                ];
            })->reverse()->values()->toArray();

            return [
                'user_growth' => $last30Days,
                'conversation_trends' => $last30Days,
                'revenue_trends' => $last30Days,
                'gender_distribution' => $this->userRepository->getGenderDistribution(),
                'language_distribution' => $this->userRepository->getLanguageDistribution(),
                'premium_vs_regular' => $this->userRepository->getPremiumDistribution(),
            ];
        });
    }

    /**
     * Get recent activity
     */
    private function getRecentActivity(): array
    {
        return [
            'recent_users' => $this->userRepository->getRecentUsers(10),
            'recent_conversations' => $this->pairRepository->getRecentConversations(10),
            'recent_transactions' => $this->balanceTransactionRepository->getRecentTransactions(10),
            'recent_reports' => $this->reportRepository->getRecentReports(10),
        ];
    }

    /**
     * Get user statistics
     */
    private function getUserStats(): array
    {
        return [
            'total' => $this->userRepository->getTotalUsers(),
            'active' => $this->userRepository->getActiveUsersCount(7),
            'premium' => $this->userRepository->getPremiumUsersCount(),
            'banned' => $this->userRepository->getBannedUsersCount(),
            'by_gender' => $this->userRepository->getGenderDistribution(),
            'by_language' => $this->userRepository->getLanguageDistribution(),
            'by_country' => $this->userRepository->getCountryDistribution(),
        ];
    }

    /**
     * Get conversation statistics
     */
    private function getConversationStats(): array
    {
        return [
            'active_now' => $this->pairRepository->getActiveConversationsCount(),
            'total_today' => $this->pairRepository->getTotalConversationsCount(1),
            'total_week' => $this->pairRepository->getTotalConversationsCount(7),
            'avg_duration' => $this->pairRepository->getAverageConversationDuration(),
            'success_rate' => $this->pairRepository->getConversationSuccessRate(),
            'by_type' => $this->pairRepository->getConversationsByType(),
        ];
    }

    /**
     * Get financial statistics
     */
    private function getFinancialStats(): array
    {
        return [
            'total_balance' => $this->balanceTransactionRepository->getTotalBalance(),
            'revenue_today' => $this->balanceTransactionRepository->getRevenueByPeriod(1),
            'revenue_week' => $this->balanceTransactionRepository->getRevenueByPeriod(7),
            'revenue_month' => $this->balanceTransactionRepository->getRevenueByPeriod(30),
            'avg_transaction' => $this->balanceTransactionRepository->getAverageTransactionAmount(),
            'top_spenders' => $this->userRepository->getTopSpenders(10),
        ];
    }

    /**
     * Get moderation statistics
     */
    private function getModerationStats(): array
    {
        return [
            'pending_reports' => $this->reportRepository->getPendingReportsCount(),
            'resolved_reports_today' => $this->reportRepository->getResolvedReportsCount(1),
            'banned_users' => $this->userRepository->getBannedUsersCount(),
            'banned_today' => $this->userRepository->getBannedUsersCount(1),
            'auto_bans' => $this->reportRepository->getAutoBansCount(7),
            'report_types' => $this->reportRepository->getReportTypeDistribution(),
        ];
    }

    /**
     * Get analytics data
     */
    private function getAnalyticsData(): array
    {
        return [
            'user_retention' => $this->getUserRetentionData(),
            'feature_usage' => $this->getFeatureUsageData(),
            'performance_metrics' => $this->getPerformanceMetrics(),
            'geographic_data' => $this->getGeographicData(),
        ];
    }

    /**
     * Get revenue chart data
     */
    private function getRevenueChartData(): array
    {
        return collect(range(0, 29))->map(function ($day) {
            $date = Carbon::now()->subDays($day);
            return [
                'date' => $date->format('M d'),
                'revenue' => $this->balanceTransactionRepository->getRevenueByDate($date),
                'transactions' => $this->balanceTransactionRepository->getTransactionCountByDate($date),
            ];
        })->reverse()->values()->toArray();
    }

    /**
     * Get system health status
     */
    private function getSystemHealth(): array
    {
        return [
            'database' => $this->checkDatabaseHealth(),
            'cache' => $this->checkCacheHealth(),
            'queue' => $this->checkQueueHealth(),
            'api_response_time' => $this->getApiResponseTime(),
        ];
    }

    private function checkDatabaseHealth(): string
    {
        try {
            \DB::connection()->getPdo();
            return 'healthy';
        } catch (\Exception $e) {
            return 'error';
        }
    }

    private function checkCacheHealth(): string
    {
        try {
            Cache::put('health_check', 'ok', 1);
            return Cache::get('health_check') === 'ok' ? 'healthy' : 'error';
        } catch (\Exception $e) {
            return 'error';
        }
    }

    private function checkQueueHealth(): string
    {
        // Implement queue health check based on your queue driver
        return 'healthy';
    }

    private function getApiResponseTime(): float
    {
        $start = microtime(true);
        $this->userRepository->getTotalUsers();
        return round((microtime(true) - $start) * 1000, 2);
    }

    private function getUserRetentionData(): array
    {
        // Implement user retention calculation
        return [];
    }

    private function getFeatureUsageData(): array
    {
        // Implement feature usage tracking
        return [];
    }

    private function getPerformanceMetrics(): array
    {
        // Implement performance metrics
        return [];
    }

    private function getGeographicData(): array
    {
        // Implement geographic data
        return [];
    }

    /**
     * Show specific user details
     */
    public function showUser($userId)
    {
        $user = $this->userRepository->findById($userId);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json($user);
    }

    /**
     * Ban a user
     */
    public function banUser($userId)
    {
        try {
            $user = $this->userRepository->findById($userId);

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not found'], 404);
            }

            $this->bannedService->banUser($userId, 'Banned via dashboard', null, true);

            return response()->json(['success' => true, 'message' => 'User banned successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error banning user'], 500);
        }
    }

    /**
     * Unban a user
     */
    public function unbanUser($userId)
    {
        try {
            $user = $this->userRepository->findById($userId);

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not found'], 404);
            }

            $this->bannedService->unbanUser($userId);

            return response()->json(['success' => true, 'message' => 'User unbanned successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error unbanning user'], 500);
        }
    }

    /**
     * Store new word filter
     */
    public function storeWordFilter(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'word' => 'required|string|max:200',
            'word_type' => 'required|integer|in:1,2,3,4,5,6',
            'is_open_ai_check' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $word = trim(strtolower($request->word));

        // Check if word already exists
        if ($this->wordFilterRepository->wordExists($word, $request->word_type)) {
            return response()->json([
                'success' => false,
                'message' => 'Word already exists in this category'
            ], 422);
        }

        try {
            $wordFilter = $this->wordFilterRepository->create([
                'word' => $word,
                'word_type' => $request->word_type,
                'is_open_ai_check' => $request->boolean('is_open_ai_check', false),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Word filter added successfully',
                'data' => $wordFilter
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error adding word filter'
            ], 500);
        }
    }

    /**
     * Update word filter
     */
    public function updateWordFilter(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'word' => 'required|string|max:200',
            'word_type' => 'required|integer|in:1,2,3,4,5,6',
            'is_open_ai_check' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $updated = $this->wordFilterRepository->update($id, [
                'word' => trim(strtolower($request->word)),
                'word_type' => $request->word_type,
                'is_open_ai_check' => $request->boolean('is_open_ai_check', false),
            ]);

            if ($updated) {
                return response()->json([
                    'success' => true,
                    'message' => 'Word filter updated successfully'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Word filter not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating word filter'
            ], 500);
        }
    }

    /**
     * Delete word filter
     */
    public function deleteWordFilter($id)
    {
        try {
            $deleted = $this->wordFilterRepository->delete($id);

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Word filter deleted successfully'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Word filter not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting word filter'
            ], 500);
        }
    }

    /**
     * Bulk import word filters
     */
    public function bulkImportWordFilter(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'words' => 'required|string',
            'word_type' => 'required|integer|in:1,2,3,4,5,6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Split words by comma or newline
            $words = preg_split('/[,\n\r]+/', $request->words);
            $words = array_filter(array_map('trim', $words));

            $imported = $this->wordFilterRepository->bulkImport($words, $request->word_type);

            return response()->json([
                'success' => true,
                'message' => "Successfully imported {$imported} words",
                'imported_count' => $imported
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error importing words'
            ], 500);
        }
    }

    /**
     * Get word filter statistics
     */
    public function getWordFilterStats()
    {
        try {
            $stats = $this->wordFilterRepository->getStatistics();
            return response()->json(['success' => true, 'data' => $stats]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching statistics'
            ], 500);
        }
    }
}
