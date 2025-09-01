<?php

namespace App\Console\Commands;

use App\Application\Services\UserService;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Console\Command;

class ManageUserCommand extends Command
{
    protected $signature = 'telegram:manage-user {action} {user_id} {--reason=} {--days=30}';

    protected $description = 'Manage users (ban, unban, upgrade)';

    public function handle(
        UserService $userService,
        UserRepositoryInterface $userRepository
    ) {
        $action = $this->argument('action');
        $userId = $this->argument('user_id');

        $user = $userRepository->findById($userId);
        if (! $user) {
            $this->error("User with ID {$userId} not found");

            return 1;
        }

        $this->info("Managing user: {$user->getFullName()} (ID: {$user->id})");
        $this->info("Telegram ID: {$user->telegram_id}");
        $this->info('Current status: '.($user->is_banned ? 'Banned' : 'Active'));

        switch ($action) {
            case 'ban':
                return $this->banUser($userService, $user);
            case 'unban':
                return $this->unbanUser($userService, $user);
            case 'upgrade':
                return $this->upgradeUser($userService, $user);
            case 'info':
                return $this->showUserInfo($userService, $user);
            default:
                $this->error("Unknown action: {$action}");
                $this->info('Available actions: ban, unban, upgrade, info');

                return 1;
        }
    }

    private function banUser(UserService $userService, User $user): int
    {
        $reason = $this->option('reason') ?: 'Admin ban';

        if ($this->confirm("Are you sure you want to ban {$user->getFullName()}?")) {
            $success = $userService->banUser($user, $reason);

            if ($success) {
                $this->info('✅ User banned successfully');
                $this->info("Reason: {$reason}");
            } else {
                $this->error('❌ Failed to ban user');

                return 1;
            }
        }

        return 0;
    }

    private function unbanUser(UserService $userService, User $user): int
    {
        if (! $user->is_banned) {
            $this->warn('User is not banned');

            return 0;
        }

        if ($this->confirm("Are you sure you want to unban {$user->getFullName()}?")) {
            $success = $userService->unbanUser($user);

            if ($success) {
                $this->info('✅ User unbanned successfully');
            } else {
                $this->error('❌ Failed to unban user');

                return 1;
            }
        }

        return 0;
    }

    private function upgradeUser(UserService $userService, User $user): int
    {
        $days = (int) $this->option('days');

        if ($this->confirm("Are you sure you want to upgrade {$user->getFullName()} to premium for {$days} days?")) {
            $success = $userService->upgradeToPremium($user, $days);

            if ($success) {
                $this->info('✅ User upgraded to premium successfully');
                $this->info("Duration: {$days} days");
            } else {
                $this->error('❌ Failed to upgrade user');

                return 1;
            }
        }

        return 0;
    }

    private function showUserInfo(UserService $userService, User $user): int
    {
        $stats = $userService->getUserStats($user);

        $this->info("\n📋 User Information:");
        $this->info("Name: {$user->getFullName()}");
        $this->info("Username: @{$user->username}");
        $this->info("Telegram ID: {$user->telegram_id}");
        $this->info("Language: {$user->language_code}");
        $this->info('Gender: '.ucfirst($user->gender ?? 'Not set'));
        $this->info('Interest: '.ucfirst($user->interest ?? 'Not set'));
        $this->info("Age: {$user->age}");
        $this->info("Location: {$user->location}");
        $this->info('Premium: '.($user->isPremium() ? 'Yes' : 'No'));
        $this->info('Banned: '.($user->is_banned ? 'Yes' : 'No'));
        $this->info("Last Activity: {$user->last_activity_at}");
        $this->info("Created: {$user->created_at}");

        $this->info("\n📊 User Statistics:");
        $this->info("Total Pairs: {$stats['total_pairs']}");
        $this->info("Active Pairs: {$stats['active_pairs']}");
        $this->info('Can Match: '.($user->canMatch() ? 'Yes' : 'No'));

        return 0;
    }
}
