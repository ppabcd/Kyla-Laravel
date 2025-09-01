<?php

namespace App\Console\Commands;

use App\Application\Services\MatchingService;
use App\Application\Services\UserService;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Console\Command;

class TestMatchingCommand extends Command
{
    protected $signature = 'telegram:test-matching {user_id?}';

    protected $description = 'Test the matching algorithm';

    public function handle(
        MatchingService $matchingService,
        UserService $userService,
        UserRepositoryInterface $userRepository
    ) {
        $userId = $this->argument('user_id');

        if ($userId) {
            $user = $userRepository->findById($userId);
            if (! $user) {
                $this->error("User with ID {$userId} not found");

                return 1;
            }
        } else {
            // Get a random user
            $user = User::inRandomOrder()->first();
            if (! $user) {
                $this->error('No users found in database');

                return 1;
            }
        }

        $this->info("Testing matching for user: {$user->getFullName()} (ID: {$user->id})");
        $this->info("Gender: {$user->gender}, Interest: {$user->interest}, Age: {$user->age}");

        // Check if user can match
        if (! $user->canMatch()) {
            $this->warn('User cannot match - incomplete profile');
            $this->info('Missing: '.implode(', ', $this->getMissingFields($user)));

            return 0;
        }

        // Find match
        $this->info('Searching for matches...');
        $match = $matchingService->findMatch($user);

        if ($match) {
            $this->info('✅ Match found!');
            $this->info("Match: {$match->getFullName()} (ID: {$match->id})");
            $this->info("Gender: {$match->gender}, Interest: {$match->interest}, Age: {$match->age}");

            // Calculate match score
            $score = $this->calculateMatchScore($user, $match);
            $this->info("Match Score: {$score}/100");

            // Ask if user wants to create pair
            if ($this->confirm('Do you want to create a pair?')) {
                $pair = $matchingService->createPair($user, $match);
                if ($pair) {
                    $this->info("✅ Pair created successfully! Pair ID: {$pair->id}");
                } else {
                    $this->error('❌ Failed to create pair');
                }
            }
        } else {
            $this->warn('❌ No suitable matches found');
        }

        // Show stats
        $stats = $matchingService->getMatchStats();
        $this->info("\n📊 Match Statistics:");
        $this->info("Active Pairs: {$stats['active_pairs']}");
        $this->info("Total Users: {$stats['total_users']}");
        $this->info("Premium Users: {$stats['premium_users']}");
        $this->info("Match Rate: {$stats['match_rate']}%");

        return 0;
    }

    private function getMissingFields(User $user): array
    {
        $missing = [];

        if (! $user->gender) {
            $missing[] = 'gender';
        }
        if (! $user->interest) {
            $missing[] = 'interest';
        }
        if (! $user->age) {
            $missing[] = 'age';
        }

        return $missing;
    }

    private function calculateMatchScore(User $user1, User $user2): int
    {
        $score = 0;

        // Base compatibility (50 points)
        if ($user1->gender === $user2->interest && $user2->gender === $user1->interest) {
            $score += 50;
        }

        // Age compatibility (20 points)
        if ($user1->age && $user2->age) {
            $ageDiff = abs($user1->age - $user2->age);
            if ($ageDiff <= 5) {
                $score += 20;
            } elseif ($ageDiff <= 10) {
                $score += 10;
            }
        }

        // Location compatibility (15 points)
        if ($user1->location && $user2->location && $user1->location === $user2->location) {
            $score += 15;
        }

        // Premium status bonus (10 points)
        if ($user1->isPremium() && $user2->isPremium()) {
            $score += 10;
        }

        // Activity bonus (5 points)
        if ($user1->last_activity_at && $user2->last_activity_at) {
            $user1Activity = $user1->last_activity_at->diffInHours(now());
            $user2Activity = $user2->last_activity_at->diffInHours(now());

            if ($user1Activity <= 1 && $user2Activity <= 1) {
                $score += 5;
            }
        }

        return min($score, 100);
    }
}
