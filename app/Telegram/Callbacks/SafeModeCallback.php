<?php

namespace App\Telegram\Callbacks;

use App\Telegram\Core\BaseCallback;
use App\Telegram\Contracts\CallbackInterface;
use App\Telegram\Core\TelegramContext;
use App\Application\Services\UserService;
use App\Domain\Repositories\UserRepositoryInterface;

class SafeModeCallback extends BaseCallback implements CallbackInterface
{
    protected string|array $callbackName = ['toggle_safe_mode', 'mode'];

    public function __construct(
        private UserService $userService,
        private UserRepositoryInterface $userRepository
    ) {}

    public function handle(\App\Telegram\Contracts\TelegramContextInterface $context): void

    {
        $telegramUser = $context->getUser();
        if (!$telegramUser) {
            $context->reply('❌ Unable to identify user');
            return;
        }
        $user = $this->userService->findOrCreateUser($telegramUser);
        $callbackData = $context->getCallbackQuery()['data'] ?? '';
        switch ($callbackData) {
            case 'toggle_safe_mode':
                $this->toggleSafeMode($context, $user);
                break;
            case 'mode':
                $this->showMode($context, $user);
                break;
        }
    }

    private function toggleSafeMode(TelegramContext $context, $user): void
    {
        $newSafeMode = !($user->safe_mode ?? true);
        $this->userService->updateUserProfile($user, ['safe_mode' => $newSafeMode]);
        $context->reply(__('safe_mode.change_success'));
    }

    private function showMode(TelegramContext $context, $user): void
    {
        $modeText = ($user->safe_mode ?? true) ? __('safe_mode.on') : __('safe_mode.off');
        $message = __('safe_mode.message', ['mode' => $modeText]);
        $keyboard = [
            [
                ['text' => __('safe_mode.toggle'), 'callback_data' => 'toggle_safe_mode']
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
