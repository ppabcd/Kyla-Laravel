<?php

namespace App\Services;

use App\Domain\Entities\User;
use App\Telegram\Contracts\TelegramContextInterface;
use App\Telegram\Services\KeyboardService;

class OnboardingService
{
    public function __construct(
        private KeyboardService $keyboardService
    ) {}

    /**
     * Check what the user needs next and guide them through onboarding
     */
    public function guideUserToNextStep(TelegramContextInterface $context, User $user): bool
    {
        // Check if user has gender
        if (! $user->gender) {
            $context->sendMessage(
                __('messages.gender.not_set', [], $user->language_code ?? 'en'),
                ['reply_markup' => $this->keyboardService->getGenderKeyboard()]
            );

            return true;
        }

        // Check if user has interest
        if (! $user->interest) {
            $context->sendMessage(
                __('messages.interest.not_set', [], $user->language_code ?? 'en'),
                ['reply_markup' => $this->keyboardService->getInterestKeyboard($user)]
            );

            return true;
        }

        // User has completed basic setup, send welcome message
        $this->sendSetupCompleteMessage($context, $user);

        return true;
    }

    /**
     * Send setup complete message with next actions
     */
    private function sendSetupCompleteMessage(TelegramContextInterface $context, User $user): void
    {
        $message = __('messages.onboarding.complete', [], $user->language_code ?? 'en')
                ?? "🎉 Great! Your profile is now complete!\n\nYou can now start searching for conversations.";

        $context->sendMessage(
            $message,
            ['reply_markup' => $this->keyboardService->getSearchKeyboard()]
        );
    }

    /**
     * Check if user profile is complete
     */
    public function isProfileComplete(User $user): bool
    {
        return ! empty($user->gender) && ! empty($user->interest);
    }

    /**
     * Get the next required field for the user
     */
    public function getNextRequiredField(User $user): ?string
    {
        if (! $user->gender) {
            return 'gender';
        }

        if (! $user->interest) {
            return 'interest';
        }

        return null;
    }
}
