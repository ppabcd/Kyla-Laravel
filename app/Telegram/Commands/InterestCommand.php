<?php

namespace App\Telegram\Commands;

use App\Application\Services\UserService;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Telegram\Contracts\CommandInterface;
use App\Telegram\Contracts\TelegramContextInterface;
use App\Telegram\Core\BaseCommand;

class InterestCommand extends BaseCommand implements CommandInterface
{
    protected string|array $commandName = 'interest';

    public function __construct(
        private UserService $userService,
        private UserRepositoryInterface $userRepository
    ) {}

    public function handle(TelegramContextInterface $context): void
    {
        $telegramUser = $context->getUser();
        if (! $telegramUser) {
            $context->reply('❌ Unable to identify user');

            return;
        }
        $user = $this->userService->findOrCreateUser($telegramUser);
        $message = __('interest.not_set');

        // Build keyboard with only opposite gender option
        $keyboard = [];

        if ($user->gender === 'male') {
            $keyboard[] = [
                ['text' => '👩 Female', 'callback_data' => 'interest-female'],
                ['text' => '🎲 Random', 'callback_data' => 'interest-all'],
            ];
        } elseif ($user->gender === 'female') {
            $keyboard[] = [
                ['text' => '👨 Male', 'callback_data' => 'interest-male'],
                ['text' => '🎲 Random', 'callback_data' => 'interest-all'],
            ];
        } else {
            // Fallback if gender not set
            $keyboard[] = [
                ['text' => '👨 Male', 'callback_data' => 'interest-male'],
                ['text' => '👩 Female', 'callback_data' => 'interest-female'],
                ['text' => '🎲 Random', 'callback_data' => 'interest-all'],
            ];
        }
        $keyboard[] = [['text' => '🔙 Back', 'callback_data' => 'profile-back']];
        $context->reply($message, [
            'reply_markup' => [
                'inline_keyboard' => $keyboard,
            ],
        ]);
    }
}
