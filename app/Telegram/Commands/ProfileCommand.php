<?php

namespace App\Telegram\Commands;

use App\Telegram\Core\BaseCommand;
use App\Telegram\Contracts\CommandInterface;
use App\Telegram\Core\TelegramContext;
use App\Application\Services\UserService;
use App\Domain\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Log;

class ProfileCommand extends BaseCommand implements CommandInterface
{
    protected string|array $commandName = 'profile';

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
            
            $this->showProfile($context, $user);

        } catch (\Exception $e) {
            Log::error('Error in ProfileCommand', [
                'error' => $e->getMessage(),
                'user_id' => $telegramUser['id'] ?? null
            ]);
            
            $context->reply('❌ An error occurred. Please try again later.');
        }
    }

    private function showProfile(TelegramContext $context, $user): void
    {
        $message = "📋 **Your Profile**\n\n";
        $message .= "👤 **Name:** " . $user->getFullName() . "\n";
        $message .= "🔤 **Language:** " . strtoupper($user->language_code ?? 'en') . "\n";
        $message .= "⚧ **Gender:** " . ucfirst($user->gender ?? 'Not set') . "\n";
        $message .= "💕 **Interest:** " . ucfirst($user->interest ?? 'Not set') . "\n";
        $message .= "🎂 **Age:** " . ($user->age ?? 'Not set') . "\n";
        $message .= "📍 **Location:** " . ($user->location ?? 'Not set') . "\n";
        $message .= "💎 **Premium:** " . ($user->isPremium() ? 'Yes' : 'No') . "\n";
        $message .= "🚫 **Status:** " . ($user->is_banned ? 'Banned' : 'Active') . "\n\n";

        if (!$user->canMatch()) {
            $message .= "⚠️ **Profile Status:** Incomplete\n";
            $message .= "Please complete your profile to start matching.\n\n";
        } else {
            $message .= "✅ **Profile Status:** Complete\n";
            $message .= "You can start matching with /next\n\n";
        }

        $message .= "Use the buttons below to update your profile:";

        $keyboard = [
            [
                ['text' => '⚧ Gender', 'callback_data' => 'profile-gender'],
                ['text' => '💕 Interest', 'callback_data' => 'profile-interest']
            ],
            [
                ['text' => '🎂 Age', 'callback_data' => 'profile-age'],
                ['text' => '📍 Location', 'callback_data' => 'profile-location']
            ],
            [
                ['text' => '🔤 Language', 'callback_data' => 'profile-language'],
                ['text' => '⚙️ Settings', 'callback_data' => 'profile-settings']
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
