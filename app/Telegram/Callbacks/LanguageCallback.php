<?php

namespace App\Telegram\Callbacks;

use App\Application\Services\UserService;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Services\OnboardingService;
use App\Telegram\Contracts\CallbackInterface;
use App\Telegram\Core\BaseCallback;
use App\Telegram\Core\TelegramContext;
use Illuminate\Support\Facades\Log;

class LanguageCallback extends BaseCallback implements CallbackInterface
{
    protected string|array $callbackName = ['lang-id', 'lang-en', 'lang-in', 'lang-my', 'lang-contribute'];

    public function __construct(
        private UserService $userService,
        private UserRepositoryInterface $userRepository,
        private OnboardingService $onboardingService
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

            if ($callbackData === 'lang-contribute') {
                $this->contributeLanguage($context);

                return;
            }

            $languageData = $this->extractLanguageFromCallback($callbackData);

            if (! $languageData) {
                $context->reply('❌ Invalid language selection');

                return;
            }

            // Find or create user
            $user = $this->userService->findOrCreateUser($telegramUser);

            // Update user language
            $success = $this->userService->updateUserProfile($user, [
                'language_code' => $languageData['language'],
            ]);

            if ($success) {
                $context->reply('✅ Language changed to: '.strtoupper($languageData['language']));

                Log::info('User updated language', [
                    'user_id' => $user->id,
                    'language' => $languageData['language'],
                    'country' => $languageData['country'],
                ]);

                // Automatically guide user to next step in onboarding
                $updatedUser = $this->userService->findOrCreateUser($telegramUser);
                $this->onboardingService->guideUserToNextStep($context, $updatedUser);
            } else {
                $context->reply('❌ Failed to update language. Please try again.');
            }

        } catch (\Exception $e) {
            Log::error('Error in LanguageCallback', [
                'error' => $e->getMessage(),
                'user_id' => $telegramUser['id'] ?? null,
            ]);

            $context->reply('❌ An error occurred. Please try again later.');
        }
    }

    private function extractLanguageFromCallback(string $callbackData): ?array
    {
        return match ($callbackData) {
            'lang-id' => ['language' => 'id', 'country' => 'ID'],
            'lang-en' => ['language' => 'en', 'country' => 'US'],
            'lang-in' => ['language' => 'in', 'country' => 'IN'],
            'lang-my' => ['language' => 'ms', 'country' => 'MY'],
            default => null
        };
    }

    private function contributeLanguage(TelegramContext $context): void
    {
        $message = "🌍 **Contribute Translation**\n\n".
            "We're always looking for help to translate Kyla Bot into more languages!\n\n".
            "If you'd like to contribute translations, please:\n".
            "1. Join our translation team\n".
            "2. Help translate strings to your language\n".
            "3. Test the translations\n\n".
            "Contact us at: support@kyla.my.id\n\n".
            'Thank you for helping make Kyla Bot accessible to more people! 🙏';

        $keyboard = [
            [
                ['text' => '🔗 Join Translation Team', 'url' => 'https://t.me/kyla_translation'],
            ],
            [
                ['text' => '📧 Contact Support', 'url' => 'mailto:support@kyla.my.id'],
            ],
            [
                ['text' => '🔙 Back', 'callback_data' => 'profile-back'],
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
