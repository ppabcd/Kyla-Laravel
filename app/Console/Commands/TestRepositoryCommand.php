<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Domain\Repositories\PairRepositoryInterface;
use App\Application\Services\UserService;
use App\Application\Services\MatchingService;
use App\Domain\Entities\User;

class TestRepositoryCommand extends Command
{
    protected $signature = 'telegram:test-repository';
    protected $description = 'Test repository pattern implementation';

    public function handle(
        UserRepositoryInterface $userRepository,
        PairRepositoryInterface $pairRepository,
        UserService $userService,
        MatchingService $matchingService
    ) {
        $this->info('🧪 Testing Repository Pattern Implementation');
        $this->newLine();

        // Test 1: User Repository
        $this->info('1. Testing User Repository');
        $this->testUserRepository($userRepository);
        $this->newLine();

        // Test 2: Pair Repository
        $this->info('2. Testing Pair Repository');
        $this->testPairRepository($pairRepository);
        $this->newLine();

        // Test 3: User Service
        $this->info('3. Testing User Service');
        $this->testUserService($userService);
        $this->newLine();

        // Test 4: Matching Service
        $this->info('4. Testing Matching Service');
        $this->testMatchingService($matchingService);
        $this->newLine();

        $this->info('✅ Repository Pattern tests completed!');
        return 0;
    }

    private function testUserRepository(UserRepositoryInterface $userRepository): void
    {
        // Test findById
        $user = User::first();
        if ($user) {
            $foundUser = $userRepository->findById($user->id);
            $this->info("   ✅ findById: " . ($foundUser ? "User found (ID: {$foundUser->id})" : "User not found"));
        }

        // Test findByTelegramId
        if ($user) {
            $foundUser = $userRepository->findByTelegramId($user->telegram_id);
            $this->info("   ✅ findByTelegramId: " . ($foundUser ? "User found (Telegram ID: {$foundUser->telegram_id})" : "User not found"));
        }

        // Test counts
        $activeUsers = $userRepository->countActiveUsers();
        $premiumUsers = $userRepository->countPremiumUsers();
        $this->info("   ✅ Counts: Active users: {$activeUsers}, Premium users: {$premiumUsers}");

        // Test findActiveUsers
        $activeUsersList = $userRepository->findActiveUsers();
        $this->info("   ✅ findActiveUsers: Found " . $activeUsersList->count() . " active users");
    }

    private function testPairRepository(PairRepositoryInterface $pairRepository): void
    {
        // Test counts
        $activePairs = $pairRepository->countActivePairs();
        $this->info("   ✅ countActivePairs: {$activePairs} active pairs");

        // Test findActivePairs
        $activePairsList = $pairRepository->findActivePairs();
        $this->info("   ✅ findActivePairs: Found " . $activePairsList->count() . " active pairs");

        // Test findRecentPairs
        $recentPairs = $pairRepository->findRecentPairs(5);
        $this->info("   ✅ findRecentPairs: Found " . $recentPairs->count() . " recent pairs");

        // Test findPairsByStatus
        $endedPairs = $pairRepository->findPairsByStatus('ended');
        $this->info("   ✅ findPairsByStatus: Found " . $endedPairs->count() . " ended pairs");
    }

    private function testUserService(UserService $userService): void
    {
        // Test getActiveUsersCount
        $activeCount = $userService->getActiveUsersCount();
        $this->info("   ✅ getActiveUsersCount: {$activeCount} active users");

        // Test getPremiumUsersCount
        $premiumCount = $userService->getPremiumUsersCount();
        $this->info("   ✅ getPremiumUsersCount: {$premiumCount} premium users");

        // Test findMatchableUsers
        $user = User::first();
        if ($user && $user->canMatch()) {
            $matchableUsers = $userService->findMatchableUsers($user);
            $this->info("   ✅ findMatchableUsers: Found " . count($matchableUsers) . " matchable users for user {$user->id}");
        } else {
            $this->info("   ⚠️ findMatchableUsers: User cannot match (incomplete profile or no users)");
        }
    }

    private function testMatchingService(MatchingService $matchingService): void
    {
        // Test getMatchStats
        $stats = $matchingService->getMatchStats();
        $this->info("   ✅ getMatchStats:");
        $this->info("      - Active pairs: {$stats['active_pairs']}");
        $this->info("      - Total users: {$stats['total_users']}");
        $this->info("      - Premium users: {$stats['premium_users']}");
        $this->info("      - Match rate: {$stats['match_rate']}%");

        // Test findMatch
        $user = User::first();
        if ($user && $user->canMatch()) {
            $match = $matchingService->findMatch($user);
            if ($match) {
                $this->info("   ✅ findMatch: Found match for user {$user->id} -> user {$match->id}");
            } else {
                $this->info("   ⚠️ findMatch: No suitable match found for user {$user->id}");
            }
        } else {
            $this->info("   ⚠️ findMatch: User cannot match (incomplete profile or no users)");
        }
    }
} 
