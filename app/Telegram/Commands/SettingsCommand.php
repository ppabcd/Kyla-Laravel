<?php

namespace App\Telegram\Commands;

use App\Telegram\Core\BaseCommand;
use App\Telegram\Contracts\CommandInterface;
use App\Telegram\Core\TelegramContext;
use App\Application\Services\UserService;
use App\Domain\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Log;

class SettingsCommand extends BaseCommand implements CommandInterface
{
    protected string|array $commandName = 'settings';

    public function __construct(
        private UserService $userService,
        private UserRepositoryInterface $userRepository
    ) {}

    public function handle(\App\Telegram\Contracts\TelegramContextInterface $context): void
    {
        try {
            $telegramUser = $context->getFrom();
            if (!$telegramUser) {
                $context->reply('❌ Unable to identify user');
                return;
            }

            // Find or create user
            $user = $this->userService->findOrCreateUser($telegramUser);
            
            $this->showSettings($context, $user);

        } catch (\Exception $e) {
            Log::error('Error in SettingsCommand', [
                'error' => $e->getMessage(),
                'user_id' => $telegramUser['id'] ?? null
            ]);
            
            $context->reply('❌ An error occurred. Please try again later.');
        }
    }

    private function showSettings(TelegramContext $context, $user): void
    {
        $message = __("settings.title") . "\n\n";
        $message .= __("help");
        $message .= "**Current Settings:**\n";
        $message .= "🔔 Notifications: " . ($user->settings['notifications'] ?? true ? 'On' : 'Off') . "\n";
        $message .= "🔒 Privacy: " . ucfirst($user->settings['privacy'] ?? 'public') . "\n";
        $message .= "🛡️ Safe Mode: " . ($user->settings['safe_mode'] ?? true ? 'On' : 'Off') . "\n";

        $keyboard = [
            [
                ['text' => '🔔 Notifications', 'callback_data' => 'settings-notifications'],
                ['text' => '🔒 Privacy', 'callback_data' => 'settings-privacy']
            ],
            [
                ['text' => '🛡️ Safe Mode', 'callback_data' => 'settings-safe-mode'],
                ['text' => '🔤 Language', 'callback_data' => 'settings-language']
            ],
            [
                ['text' => '📊 Data & Privacy', 'callback_data' => 'settings-data-privacy'],
                ['text' => '❌ Delete Account', 'callback_data' => 'settings-delete-account']
            ],
            [
                ['text' => '🔙 Back to Profile', 'callback_data' => 'profile-back']
            ]
        ];

        $context->reply($message, [
            'reply_markup' => [
                'inline_keyboard' => $keyboard
            ],
            'parse_mode' => 'Markdown'
        ]);
    }
} 
