<?php

namespace App\Telegram\Commands;

use App\Application\Services\UserService;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Telegram\Contracts\CommandInterface;
use App\Telegram\Core\BaseCommand;
use Illuminate\Support\Facades\Log;

class TestCommand extends BaseCommand implements CommandInterface
{
    protected string|array $commandName = 'test';

    public function __construct(
        private UserService $userService,
        private UserRepositoryInterface $userRepository
    ) {}

    public function handle(\App\Telegram\Contracts\TelegramContextInterface $context): void
    {
        try {
            $telegramUser = $context->getUser();
            if (! $telegramUser) {
                $context->reply('❌ Unable to identify user');

                return;
            }

            // Check if user is admin
            if (! $this->isAdmin($telegramUser)) {
                $context->reply('❌ Access denied. Admin privileges required.');

                return;
            }

            $message = "🧪 **Test Command**\n\n";
            $message .= "Bot is working correctly!\n";
            $message .= 'Server time: '.now()->format('Y-m-d H:i:s')."\n";
            $message .= 'User ID: '.$telegramUser['id']."\n";
            $message .= 'Username: @'.($telegramUser['username'] ?? 'N/A');

            $context->reply($message, [
                'parse_mode' => 'Markdown',
                'reply_to_message_id' => $context->getMessage()['message_id'] ?? null,
            ]);

            Log::info('Test command executed by admin', [
                'user_id' => $telegramUser['id'],
                'username' => $telegramUser['username'] ?? null,
            ]);

        } catch (\Exception $e) {
            Log::error('Error in TestCommand', [
                'error' => $e->getMessage(),
                'user_id' => $telegramUser['id'] ?? null,
            ]);

            $context->reply('❌ An error occurred during testing.');
        }
    }

    private function isAdmin(array $telegramUser): bool
    {
        $adminIds = config('telegram.admin_ids', []);

        return in_array($telegramUser['id'], $adminIds);
    }
}
