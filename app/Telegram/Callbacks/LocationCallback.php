<?php

namespace App\Telegram\Callbacks;

use App\Telegram\Core\BaseCallback;
use App\Telegram\Contracts\CallbackInterface;
use App\Telegram\Core\TelegramContext;
use App\Application\Services\UserService;
use App\Domain\Repositories\UserRepositoryInterface;

class LocationCallback extends BaseCallback implements CallbackInterface
{
    protected string|array $callbackName = ['location'];

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
        
        $message = __('location.ask');
        
        $keyboard = [
            [
                ['text' => '📍 Share Location', 'callback_data' => 'location-share']
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
        
        // Note: In a real implementation, you would set conversation state
        // to wait for the user's location input
    }
} 
