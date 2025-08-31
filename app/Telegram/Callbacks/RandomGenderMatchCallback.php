<?php

namespace App\Telegram\Callbacks;

use App\Application\Services\UserService;
use App\Infrastructure\Repositories\PairPendingRepository;
use App\Telegram\Contracts\CallbackInterface;
use App\Telegram\Contracts\TelegramContextInterface;
use App\Telegram\Core\BaseCallback;
use App\Telegram\Services\KeyboardService;

class RandomGenderMatchCallback extends BaseCallback implements CallbackInterface
{
    protected string|array $callbackName = 'random_gender_match';

    public function __construct(
        private UserService $userService,
        private PairPendingRepository $pairPendingRepository,
        private KeyboardService $keyboardService
    ) {}

    public function handle(TelegramContextInterface $context): void
    {
        $telegramUser = $context->getUser();
        if (! $telegramUser) {
            $context->answerCallbackQuery(__('User not found'));

            return;
        }

        $user = $this->userService->findOrCreateUser($telegramUser);

        $userPending = $this->pairPendingRepository->findByUserId($user->id);
        if ($userPending) {
            $this->pairPendingRepository->update($userPending, [
                'interest' => null,
            ]);

            $message = __('messages.queue.random_match_enabled');
            $keyboard = $this->keyboardService->getSearchingKeyboard();

            $context->editMessageText($message, $keyboard);
            $context->answerCallbackQuery(__('✅ Switched to random gender matching'));
        } else {
            $context->answerCallbackQuery(__('❌ You are not in the queue'));
        }
    }
}
