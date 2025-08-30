<?php

namespace App\Telegram\Callbacks;

use App\Application\Services\UserService;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Services\OnboardingService;
use App\Telegram\Contracts\CallbackInterface;
use App\Telegram\Core\BaseCallback;
use App\Telegram\Core\TelegramContext;
use Illuminate\Support\Facades\Log;

class InterestCallback extends BaseCallback implements CallbackInterface
{
    protected string|array $callbackName = ['interest-male', 'interest-female', 'interest-all'];

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
            $interest = $this->extractInterestFromCallback($callbackData);

            if (! $interest) {
                $context->reply('❌ Invalid interest selection');

                return;
            }

            // Find or create user
            $user = $this->userService->findOrCreateUser($telegramUser);

            // Update user interest
            $success = $this->userService->updateUserProfile($user, ['interest' => $interest]);

            if ($success) {
                $context->reply('✅ Interest set to: '.ucfirst($interest));

                Log::info('User updated interest', [
                    'user_id' => $user->id,
                    'interest' => $interest,
                ]);

                // Automatically guide user to next step in onboarding
                $updatedUser = $this->userService->findOrCreateUser($telegramUser);
                $this->onboardingService->guideUserToNextStep($context, $updatedUser);
            } else {
                $context->reply('❌ Failed to update interest. Please try again.');
            }

        } catch (\Exception $e) {
            Log::error('Error in InterestCallback', [
                'error' => $e->getMessage(),
                'user_id' => $telegramUser['id'] ?? null,
            ]);

            $context->reply('❌ An error occurred. Please try again later.');
        }
    }

    private function extractInterestFromCallback(string $callbackData): ?string
    {
        return match ($callbackData) {
            'interest-male' => 'male',
            'interest-female' => 'female',
            'interest-all' => 'all',
            default => null
        };
    }

    private function askForAge(TelegramContext $context): void
    {
        $message = "How old are you?\n\n";
        $message .= 'Please enter your age (18-65):';

        $context->reply($message);
        // Note: In a real implementation, you might want to use conversation state
        // to handle text input for age
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
