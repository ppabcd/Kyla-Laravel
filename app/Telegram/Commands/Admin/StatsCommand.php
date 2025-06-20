<?php

namespace App\Telegram\Commands\Admin;

use App\Telegram\Core\BaseCommand;
use App\Telegram\Contracts\TelegramContextInterface;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Application\Services\BannedService;

class StatsCommand extends BaseCommand
{
    protected string $name = 'stats';
    protected string $description = 'Show bot statistics';
    protected bool $adminOnly = true;

    public function __construct(
        private UserRepositoryInterface $userRepository,
        private BannedService $bannedService
    ) {}

    public function handle(TelegramContextInterface $context): void
    {
        $message = $context->getMessage();
        $chatId = $message->chat->id;

        // Check if user is admin
        if (!$this->isAdmin($chatId)) {
            $context->reply(__('errors.permission_denied'));
            return;
        }

        // Get statistics
        $totalUsers = $this->userRepository->countAll();
        $activeUsers = $this->userRepository->countActive();
        $bannedUsers = $this->bannedService->getBannedCount();
        $todayUsers = $this->userRepository->countToday();
        $weekUsers = $this->userRepository->countThisWeek();
        $monthUsers = $this->userRepository->countThisMonth();

        $stats = "📊 **Bot Statistics**\n\n";
        $stats .= "👥 **Total Users:** {$totalUsers}\n";
        $stats .= "✅ **Active Users:** {$activeUsers}\n";
        $stats .= "🚫 **Banned Users:** {$bannedUsers}\n";
        $stats .= "📅 **Today:** {$todayUsers}\n";
        $stats .= "📅 **This Week:** {$weekUsers}\n";
        $stats .= "📅 **This Month:** {$monthUsers}\n\n";
        $stats .= "🔄 **Last Updated:** " . now()->format('Y-m-d H:i:s');

        $context->reply($stats);
    }

    private function isAdmin(int $chatId): bool
    {
        $adminIds = config('telegram.admin_ids', []);
        return in_array($chatId, $adminIds);
    }
} 
