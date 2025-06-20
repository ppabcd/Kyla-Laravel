<?php

namespace App\Telegram\Commands\Admin;

use App\Telegram\Core\BaseCommand;
use App\Telegram\Contracts\TelegramContextInterface;
use App\Domain\Entities\User;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Application\Services\BannedService;

class UnbanCommand extends BaseCommand
{
    protected string $name = 'unban';
    protected string $description = 'Unban a user';
    protected bool $adminOnly = true;

    public function __construct(
        private UserRepositoryInterface $userRepository,
        private BannedService $bannedService
    ) {}

    public function handle(TelegramContextInterface $context): void
    {
        $message = $context->getMessage();
        $chatId = $message->chat->id;
        $text = $message->text ?? '';

        // Check if user is admin
        if (!$this->isAdmin($chatId)) {
            $context->reply(__('errors.permission_denied'));
            return;
        }

        // Extract user ID
        $parts = explode(' ', $text, 2);
        if (count($parts) < 2) {
            $context->reply("✅ **Unban User**\n\nGunakan format:\n/unban <user_id>\n\nContoh:\n/unban 123456789");
            return;
        }

        $targetUserId = (int) $parts[1];

        // Find user
        $user = $this->userRepository->findByTelegramId($targetUserId);
        if (!$user) {
            $context->reply(__('errors.user_not_found'));
            return;
        }

        // Check if not banned
        if (!$this->bannedService->isUserBanned($targetUserId)) {
            $context->reply(__('errors.not_banned'));
            return;
        }

        // Unban user
        $this->bannedService->unbanUser($targetUserId);

        $context->reply("✅ **User Unbanned**\n\nUser ID: {$targetUserId}\nUsername: @{$user->username}\n\nUnbanned by: " . $this->getAdminUsername($chatId));
    }

    private function isAdmin(int $chatId): bool
    {
        $adminIds = config('telegram.admin_ids', []);
        return in_array($chatId, $adminIds);
    }

    private function getAdminUsername(int $chatId): string
    {
        return "Admin ({$chatId})";
    }
} 
