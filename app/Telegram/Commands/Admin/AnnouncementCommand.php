<?php

namespace App\Telegram\Commands\Admin;

use App\Domain\Repositories\UserRepositoryInterface;
use App\Telegram\Contracts\TelegramContextInterface;
use App\Telegram\Core\BaseCommand;

class AnnouncementCommand extends BaseCommand
{
    protected string $name = 'announcement';

    protected string $description = 'Make an announcement to all users';

    protected bool $adminOnly = true;

    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function handle(TelegramContextInterface $context): void
    {
        $message = $context->getMessage();
        $chatId = $message['chat']['id'] ?? null;
        $text = $message['text'] ?? '' ?? '';

        // Check if user is admin
        if (! $this->isAdmin($chatId)) {
            $context->reply(__('errors.permission_denied'));

            return;
        }

        // Extract announcement text (remove /announcement command)
        $announcementText = trim(str_replace('/announcement', '', $text));

        if (empty($announcementText)) {
            $context->reply("📢 **Pengumuman**\n\nGunakan format:\n/announcement <teks pengumuman>\n\nContoh:\n/announcement Server akan maintenance pada pukul 02:00 WIB");

            return;
        }

        // Get all users
        $users = $this->userRepository->findAll();

        $successCount = 0;
        $failedCount = 0;

        foreach ($users as $user) {
            try {
                $context->getBot()->sendMessage([
                    'chat_id' => $user->telegram_id,
                    'text' => "📢 **PENGUMUMAN**\n\n{$announcementText}\n\n— Tim Kyla Bot",
                    'parse_mode' => 'Markdown',
                ]);
                $successCount++;
            } catch (\Exception $e) {
                $failedCount++;
            }
        }

        $context->reply("📢 **Pengumuman Terkirim**\n\n✅ Berhasil: {$successCount} pengguna\n❌ Gagal: {$failedCount} pengguna\n\nTotal: ".count($users).' pengguna');
    }

    private function isAdmin(int $chatId): bool
    {
        // Get admin IDs from config
        $adminIds = config('telegram.admin_ids', []);

        return in_array($chatId, $adminIds);
    }
}
