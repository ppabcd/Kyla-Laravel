<?php

namespace App\Telegram\Callbacks;

use App\Application\Services\UserService;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Services\OnboardingService;
use App\Telegram\Contracts\CallbackInterface;
use App\Telegram\Core\BaseCallback;
use App\Telegram\Core\TelegramContext;
use Illuminate\Support\Facades\Log;

class GenderCallback extends BaseCallback implements CallbackInterface
{
    protected string|array $callbackName = ['gender-male', 'gender-female'];

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
            $gender = $this->extractGenderFromCallback($callbackData);

            if (! $gender) {
                $context->reply('❌ Invalid gender selection');

                return;
            }

            // Find or create user
            $user = $this->userService->findOrCreateUser($telegramUser);

            // Update user gender
            $success = $this->userService->updateUserProfile($user, ['gender' => $gender]);

            if ($success) {
                $context->reply('✅ Gender set to: '.ucfirst($gender));

                Log::info('User updated gender', [
                    'user_id' => $user->id,
                    'gender' => $gender,
                ]);

                // Automatically guide user to next step in onboarding
                $updatedUser = $this->userService->findOrCreateUser($telegramUser);
                $this->onboardingService->guideUserToNextStep($context, $updatedUser);
            } else {
                $context->reply('❌ Failed to update gender. Please try again.');
            }

        } catch (\Exception $e) {
            Log::error('Error in GenderCallback', [
                'error' => $e->getMessage(),
                'user_id' => $telegramUser['id'] ?? null,
            ]);

            $context->reply('❌ An error occurred. Please try again later.');
        }
    }

    private function extractGenderFromCallback(string $callbackData): ?string
    {
        return match ($callbackData) {
            'gender-male' => 'male',
            'gender-female' => 'female',
            default => null
        };
    }

    private function askForInterest(TelegramContext $context): void
    {
        $user = $context->getUserModel();
        $userGender = $user->gender ?? '';

        $message = "Great! Now, what gender are you interested in?\n\n";
        $message .= 'Please select your preference:';

        // Only show opposite gender and random option in one row
        $keyboard = [];

        if ($userGender === 'male') {
            $keyboard[] = [
                ['text' => '👩 Female', 'callback_data' => 'interest-female'],
                ['text' => '🎲 Random', 'callback_data' => 'interest-all'],
            ];
        } elseif ($userGender === 'female') {
            $keyboard[] = [
                ['text' => '👨 Male', 'callback_data' => 'interest-male'],
                ['text' => '🎲 Random', 'callback_data' => 'interest-all'],
            ];
        }

        $context->reply($message, [
            'reply_markup' => [
                'inline_keyboard' => $keyboard,
            ],
        ]);
    }

    private function showProfileStatus(TelegramContext $context, $user): void
    {
        $message = "📋 Profile Status:\n\n";
        $message .= 'Gender: '.ucfirst($user->gender ?? 'Not set')."\n";
        $message .= 'Interest: '.ucfirst($user->interest ?? 'Not set')."\n";
        $message .= 'Age: '.($user->age ?? 'Not set')."\n\n";

        if ($user->canMatch()) {
            $message .= '✅ Your profile is complete! Use /next to find a match.';
        } else {
            $message .= '⚠️ Complete your profile to start matching.';
        }

        $context->reply($message);
    }
}
