<?php

namespace App\Telegram\Callbacks;

use App\Application\Services\UserService;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Telegram\Contracts\CallbackInterface;
use App\Telegram\Core\BaseCallback;

class AgeCallback extends BaseCallback implements CallbackInterface
{
    protected string|array $callbackName = ['age'];

    public function __construct(
        private UserService $userService,
        private UserRepositoryInterface $userRepository
    ) {}

    public function handle(\App\Telegram\Contracts\TelegramContextInterface $context): void
    {
        $telegramUser = $context->getUser();
        if (! $telegramUser) {
            $context->reply('❌ Unable to identify user');

            return;
        }

        $user = $this->userService->findOrCreateUser($telegramUser);

        $message = __('age.ask_age');

        $context->reply($message);

        // Note: In a real implementation, you would set conversation state
        // to wait for the user's age input
    }
}
