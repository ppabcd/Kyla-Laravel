<?php

namespace App\Telegram\Commands\Admin;

use App\Domain\Repositories\UserRepositoryInterface;
use App\Telegram\Contracts\TelegramContextInterface;
use App\Telegram\Core\BaseCommand;

class BannedCommand extends BaseCommand
{
    protected string $name = 'banned';

    protected string $description = 'Lihat daftar user yang sedang dibanned';

    protected bool $adminOnly = true;

    public function __construct(private UserRepositoryInterface $userRepository) {}

    public function handle(TelegramContextInterface $context): void
    {
        $chatId = $context->getChatId();
        if (! $this->isAdmin($chatId)) {
            $context->reply(__('errors.permission_denied'));

            return;
        }
        $bannedUsers = $this->userRepository->getCurrentBannedUsers();
        if (empty($bannedUsers)) {
            $context->reply(__('commands.banned.empty'));

            return;
        }
        $text = "\xF0\x9F\x9A\xAB **User yang Sedang Diblokir**\n\n";
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
