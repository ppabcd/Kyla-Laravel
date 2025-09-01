<?php

namespace App\Telegram\Commands\Admin;

use App\Application\Services\BannedService;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Models\User;
use App\Telegram\Contracts\TelegramContextInterface;
use App\Telegram\Core\BaseCommand;

class BanCommand extends BaseCommand
{
    protected string $name = 'ban';

    protected string $description = 'Ban a user';

    protected bool $adminOnly = true;

    public function __construct(
        private UserRepositoryInterface $userRepository,
        private BannedService $bannedService
    ) {}

    public function handle(TelegramContextInterface $context): void
    {
        $message = $context->getMessage();
        $chatId = $message['chat']['id'] ?? null;

        if (! $chatId) {
            $context->reply('❌ Chat ID tidak ditemukan.');

            return;
        }
        $text = $message['text'] ?? '';

        // Check if user is admin
        if (! $this->isAdmin($chatId)) {
            $context->reply(__('errors.permission_denied'));

            return;
        }

        // Extract user ID and reason
        $parts = explode(' ', $text, 3);
        if (count($parts) < 2) {
            $context->reply("🚫 **Ban User**\n\nGunakan format:\n/ban <user_id> [alasan]\n\nContoh:\n/ban 123456789 Melanggar peraturan\n/ban 123456789");

            return;
        }

        $targetUserId = (int) $parts[1];
        $reason = $parts[2] ?? 'No reason provided';

        // Find user
        $user = $this->userRepository->findByTelegramId($targetUserId);
        if (! $user) {
            $context->reply(__('errors.user_not_found'));

            return;
        }

        // Check if already banned
        if ($this->bannedService->isUserBanned($targetUserId)) {
            $context->reply(__('errors.already_banned'));

            return;
        }

        // Ban user
        $this->bannedService->banUser($targetUserId, $reason, $chatId);

        $context->reply("🚫 **User Banned**\n\nUser ID: {$targetUserId}\nUsername: @{$user->username}\nReason: {$reason}\n\nBanned by: ".$this->getAdminUsername($chatId));
    }

    private function isAdmin(int $chatId): bool
    {
        $adminIds = config('telegram.admin_ids', []);

        return in_array($chatId, $adminIds);
    }

    private function getAdminUsername(int $chatId): string
    {
        // This would typically get the admin username from the message
        return "Admin ({$chatId})";
    }
}
