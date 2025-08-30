<?php

namespace App\Telegram\Commands;

use App\Services\ConversationService;
use App\Services\MixpanelService;
use App\Telegram\Contracts\TelegramContextInterface;
use App\Telegram\Core\BaseCommand;
use Illuminate\Support\Facades\Log;

class StartCommand extends BaseCommand
{
    protected string|array $commandName = ['start', 'search'];

    protected string $description = 'Start searching for a conversation partner';

    protected string $usage = '/start';

    // Middleware handled by TelegramBotService

    public function __construct(
        private ConversationService $conversationService,
        private MixpanelService $mixpanelService
    ) {}

    public function handle(TelegramContextInterface $context): void
    {
        try {
            $user = $context->getUserModel();

            if (! $user) {
                $context->sendMessage('❌ User not found');

                return;
            }

            // Call conversation service to handle start conversation
            $this->conversationService->startConversation($context);

            // Track analytics
            $this->mixpanelService->trackEvent('Conversation action', [
                'action' => 'search',
                'distinct_id' => $user->id,
            ]);

            Log::info('Start command executed', [
                'user_id' => $user->id,
                'telegram_id' => $user->telegram_id,
                'source' => $context->getCallbackQuery() ? 'callback' : 'message',
            ]);

        } catch (\Exception $e) {
            Log::error('StartCommand error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $context->getUserId(),
            ]);

            $context->sendMessage('❌ An error occurred. Please try again.');
        }
    }
}
