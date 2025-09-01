<?php

namespace App\Telegram\Callbacks;

use App\Application\Services\UserService;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Telegram\Contracts\CallbackInterface;
use App\Telegram\Core\BaseCallback;
use App\Telegram\Core\TelegramContext;
use Illuminate\Support\Facades\Log;

class SettingsCallback extends BaseCallback implements CallbackInterface
{
    protected string|array $callbackName = [
        'settings',
        'settings-safe-mode',
        'settings-language',
        'settings-data-privacy',
        'settings-delete-account',
    ];

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

            $callbackData = $context->getCallbackQuery()['data'] ?? '';
            $user = $this->userService->findOrCreateUser($telegramUser);

            $this->handleSettingsAction($context, $user, $callbackData);

        } catch (\Exception $e) {
            Log::error('Error in SettingsCallback', [
                'error' => $e->getMessage(),
                'user_id' => $telegramUser['id'] ?? null,
            ]);

            $context->reply('❌ An error occurred. Please try again later.');
        }
    }

    private function handleSettingsAction(TelegramContext $context, $user, string $callbackData): void
    {
        switch ($callbackData) {
            case 'settings':
                $this->showSettings($context, $user);
                break;
            case 'settings-safe-mode':
                $this->toggleSafeMode($context, $user);
                break;
            case 'settings-language':
                $this->showLanguageSettings($context, $user);
                break;
            case 'settings-data-privacy':
                $this->showDataPrivacy($context, $user);
                break;
            case 'settings-delete-account':
                $this->showDeleteAccount($context, $user);
                break;
            default:
                $context->reply('❌ Invalid setting option');
        }
    }

    private function showSettings(TelegramContext $context, $user): void
    {
        $message = __('messages.settings.title');

        $keyboard = [
            [
                ['text' => '🛡️ Safe Mode', 'callback_data' => 'settings-safe-mode'],
                ['text' => '🔤 Language', 'callback_data' => 'settings-language'],
            ],
            [
                ['text' => '📊 Data & Privacy', 'callback_data' => 'settings-data-privacy'],
                ['text' => '❌ Delete Account', 'callback_data' => 'settings-delete-account'],
            ],
        ];

        $context->reply($message, [
            'reply_markup' => [
                'inline_keyboard' => $keyboard,
            ],
            'parse_mode' => 'Markdown',
        ]);
    }

    private function toggleSafeMode(TelegramContext $context, $user): void
    {
        $currentSetting = $user->settings['safe_mode'] ?? true;
        $newSetting = ! $currentSetting;

        $this->userService->updateUserProfile($user, [
            'settings' => array_merge($user->settings ?? [], ['safe_mode' => $newSetting]),
        ]);

        $status = $newSetting ? 'enabled' : 'disabled';
        $context->reply("✅ Safe mode {$status}!");
    }

    private function showLanguageSettings(TelegramContext $context, $user): void
    {
        $message = "🔤 **Language Settings**\n\n";
        $message .= 'Current language: '.strtoupper($user->language_code ?? 'en')."\n\n";
        $message .= 'Select your preferred language:';

        $keyboard = [
            [
                ['text' => '🇺🇸 English', 'callback_data' => 'lang-en'],
                ['text' => '🇮🇩 Bahasa Indonesia', 'callback_data' => 'lang-id'],
            ],
            [
                ['text' => '🇲🇾 Bahasa Melayu', 'callback_data' => 'lang-my'],
                ['text' => '🇮🇳 हिन्दी', 'callback_data' => 'lang-in'],
            ],
            [
                ['text' => '🔙 Back to Settings', 'callback_data' => 'settings-back'],
            ],
        ];

        $context->reply($message, [
            'reply_markup' => [
                'inline_keyboard' => $keyboard,
            ],
            'parse_mode' => 'Markdown',
        ]);
    }

    private function showDataPrivacy(TelegramContext $context, $user): void
    {
        $message = "📊 **Data & Privacy**\n\n";
        $message .= "**What data we collect:**\n";
        $message .= "• Your Telegram ID and basic info\n";
        $message .= "• Profile preferences (gender, age, location)\n";
        $message .= "• Chat history and interactions\n";
        $message .= "• Usage analytics\n\n";
        $message .= "**How we use your data:**\n";
        $message .= "• To provide matching services\n";
        $message .= "• To improve our algorithms\n";
        $message .= "• To ensure community safety\n";
        $message .= "• To provide customer support\n\n";
        $message .= "**Your rights:**\n";
        $message .= "• Request data deletion\n";
        $message .= "• Export your data\n";
        $message .= "• Control privacy settings\n\n";
        $message .= 'For more information, contact: support@kyla.my.id';

        $keyboard = [
            [
                ['text' => '🗑️ Delete My Data', 'callback_data' => 'data-delete'],
                ['text' => '📥 Export Data', 'callback_data' => 'data-export'],
            ],
            [
                ['text' => '🔙 Back to Settings', 'callback_data' => 'settings-back'],
            ],
        ];

        $context->reply($message, [
            'reply_markup' => [
                'inline_keyboard' => $keyboard,
            ],
            'parse_mode' => 'Markdown',
        ]);
    }

    private function showDeleteAccount(TelegramContext $context, $user): void
    {
        $message = "❌ **Delete Account**\n\n";
        $message .= "⚠️ **Warning:** This action cannot be undone!\n\n";
        $message .= "**What will be deleted:**\n";
        $message .= "• Your profile and all data\n";
        $message .= "• Chat history and conversations\n";
        $message .= "• Settings and preferences\n";
        $message .= "• All matches and connections\n\n";
        $message .= 'Are you sure you want to delete your account?';

        $keyboard = [
            [
                ['text' => '❌ Yes, Delete My Account', 'callback_data' => 'delete-confirm'],
                ['text' => '✅ No, Keep My Account', 'callback_data' => 'delete-cancel'],
            ],
        ];

        $context->reply($message, [
            'reply_markup' => [
                'inline_keyboard' => $keyboard,
            ],
            'parse_mode' => 'Markdown',
        ]);
    }
}
