<?php

namespace App\Telegram\Commands\Admin;

use App\Telegram\Core\BaseCommand;
use App\Telegram\Contracts\TelegramContextInterface;
use App\Domain\Repositories\UserRepositoryInterface;

class BanHistoryCommand extends BaseCommand
{
    protected string $name = 'banhistory';
    protected string $description = 'Lihat riwayat banned user';
    protected bool $adminOnly = true;

    public function __construct(private UserRepositoryInterface $userRepository) {}

    public function handle(TelegramContextInterface $context): void
    {
        $chatId = $context->getMessage()->chat->id;
        if (!$this->isAdmin($chatId)) {
            $context->reply(__('errors.permission_denied'));
            return;
        }
        // Ambil data banned user
        $bannedUsers = $this->userRepository->getBannedHistory();
        if (empty($bannedUsers)) {
            $context->reply(__('commands.banhistory.empty'));
            return;
        }
        $text = "\xF0\x9F\x9A\xAB **Riwayat Banned User**\n\n";
        foreach ($bannedUsers as $user) {
            $text .= "ID: {$user->telegram_id} | @{$user->username} | {$user->banned_at}\n";
        }
        $context->reply($text);
    }
    private function isAdmin(int $chatId): bool
    {
        $adminIds = config('telegram.admin_ids', []);
        return in_array($chatId, $adminIds);
    }
} 
