<?php

namespace App\Telegram\Callbacks;

use App\Telegram\Core\BaseCallback;
use App\Telegram\Contracts\CallbackInterface;
use App\Telegram\Core\TelegramContext;
use App\Application\Services\UserService;
use App\Domain\Repositories\UserRepositoryInterface;

class RatingCallback extends BaseCallback implements CallbackInterface
{
    protected string|array $callbackName = [
        'thumbs-up',
        'thumbs-down',
        'my-rating'
    ];

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
        $callbackData = $context->getCallbackQuery()['data'] ?? '';
        switch ($callbackData) {
            case 'thumbs-up':
                $this->handleThumbs($context, true);
                break;
            case 'thumbs-down':
                $this->handleThumbs($context, false);
                break;
            case 'my-rating':
                $this->handleMyRating($context, $telegramUser);
                break;
        }
    }

    private function handleThumbs(TelegramContext $context, bool $state): void
    {
        // Simulasi update rating
        $context->reply(__('rating.thank_you'));
    }

    private function handleMyRating(TelegramContext $context, $user): void
    {
        // Simulasi rating user
        $starRating = '⭐⭐⭐⭐';
        $rating = '4.00';
        $totalRating = 12;
        $message = __('rating.my_rating', [
            'starRating' => $starRating,
            'rating' => $rating,
            'totalRating' => $totalRating
        ]);
        $context->reply($message);
    }
} 
