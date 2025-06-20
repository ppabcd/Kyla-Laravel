<?php

namespace App\Telegram\Commands;

use App\Telegram\Core\BaseCommand;
use App\Telegram\Contracts\CommandInterface;
use App\Telegram\Core\TelegramContext;
use App\Application\Services\UserService;
use App\Domain\Repositories\UserRepositoryInterface;

class ModeCommand extends BaseCommand implements CommandInterface
{
    protected string|array $commandName = 'mode';

    public function __construct(
        private UserService $userService,
        private UserRepositoryInterface $userRepository
    ) {}

    public function handle(\App\Telegram\Contracts\TelegramContextInterface $context): void
    {
        $telegramUser = $context->getFrom();
        if (!$telegramUser) {
            $context->reply('❌ Unable to identify user');
            return;
        }
        $user = $this->userService->findOrCreateUser($telegramUser);
        $mode = $user->safe_mode ?? true;
        $modeText = $mode ? __('safe_mode.on') : __('safe_mode.off');
        $message = __('safe_mode.message', ['mode' => $modeText]);
        $keyboard = [
            [
                ['text' => __('safe_mode.toggle'), 'callback_data' => 'safe-mode-toggle']
            ],
            [
                ['text' => '🔙 Back', 'callback_data' => 'settings-back']
            ]
        ];
        $context->reply($message, [
            'reply_markup' => [
                'inline_keyboard' => $keyboard
            ]
        ]);
    }
} 
