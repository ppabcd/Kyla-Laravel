<?php

namespace App\Telegram\Commands;

use App\Telegram\Core\BaseCommand;
use App\Telegram\Contracts\CommandInterface;
use App\Telegram\Core\TelegramContext;
use App\Application\Services\UserService;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Telegram\Contracts\TelegramContextInterface;

class InterestCommand extends BaseCommand implements CommandInterface
{
    protected string|array $commandName = 'interest';

    public function __construct(
        private UserService $userService,
        private UserRepositoryInterface $userRepository
    ) {}

    public function handle(TelegramContextInterface $context): void
    {
        $telegramUser = $context->getFrom();
        if (!$telegramUser) {
            $context->reply('❌ Unable to identify user');
            return;
        }
        $user = $this->userService->findOrCreateUser($telegramUser);
        $message = __("interest.not_set");
        $keyboard = [
            [
                ['text' => '👨 Male', 'callback_data' => 'interest-male'],
                ['text' => '👩 Female', 'callback_data' => 'interest-female']
            ],
            [
                ['text' => '🔙 Back', 'callback_data' => 'profile-back']
            ]
        ];
        $context->reply($message, [
            'reply_markup' => [
                'inline_keyboard' => $keyboard
            ]
        ]);
    }
} 
