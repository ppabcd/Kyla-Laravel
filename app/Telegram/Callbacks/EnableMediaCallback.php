<?php

namespace App\Telegram\Callbacks;

use App\Domain\Repositories\PairRepositoryInterface;
use App\Telegram\Contracts\TelegramContextInterface;
use App\Telegram\Core\BaseCallback;
use App\Telegram\Core\TelegramContext;
use App\Telegram\Services\KeyboardService;
use Illuminate\Support\Facades\Cache;

class EnableMediaCallback extends BaseCallback
{
    protected string|array $callbackName = ['enable_media', 'enable_media_confirm'];

    public function __construct(
        private PairRepositoryInterface $pairRepository,
        private KeyboardService $keyboardService
    ) {}

    public function handle(TelegramContextInterface $context): void
    {
        $callback = $context->getCallbackData();
        $user = $context->getUserModel();
        if (! $user) {
            return;
        }

        // If user taps "Enable Media" on their side, ask the partner for confirmation
        if ($callback === 'enable_media') {
            $pair = $this->pairRepository->findActivePairByUserId($user->id);
            $partner = $pair?->getOtherUser($user->id);
            if (! $partner) {
                $context->answerCallbackQuery('No active conversation');

                return;
            }

            $message = 'Your partner requests to enable media temporarily for this conversation. Confirm?';
            TelegramContext::sendMessageToChat(
                (string) $partner->telegram_id,
                $message,
                [
                    'reply_markup' => $this->keyboardService->getConfirmationKeyboardEnableMedia(),
                ]
            );

            $context->answerCallbackQuery('Request sent to your partner');
            $context->reply('✅ Request sent. We’ll notify you once they confirm.');

            return;
        }

        // If the partner confirms, temporarily allow media for this conversation
        if ($callback === 'enable_media_confirm') {
            // Mark receiver (the confirmer) as allowing media for current conversation
            Cache::put("enable-media:{$user->id}", true, now()->addHours(4));

            $context->answerCallbackQuery('Media enabled for this conversation');
            $context->reply('✅ Media enabled temporarily for this conversation. You can revert via /mode.');

            // Notify the partner (the requester) that media is enabled now
            $pair = $this->pairRepository->findActivePairByUserId($user->id);
            $partner = $pair?->getOtherUser($user->id);
            if ($partner) {
                TelegramContext::sendMessageToChat(
                    (string) $partner->telegram_id,
                    '✅ Your partner enabled media for this conversation. You can resend your media now.'
                );
            }

            return;
        }

        // Fallback
        $context->answerCallbackQuery();
    }
}
