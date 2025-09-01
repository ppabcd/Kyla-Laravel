<?php

namespace App\Telegram\Commands;

use App\Services\ConversationService;
use App\Services\MixpanelService;
use App\Telegram\Contracts\TelegramContextInterface;
use App\Telegram\Core\BaseCommand;
use Illuminate\Support\Facades\Log;

class StopCommand extends BaseCommand
{
    protected string|array $commandName = 'stop';

    protected string $description = 'Stop current conversation';

    protected string $usage = '/stop';

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

            // Call conversation service to handle stop conversation
            $this->conversationService->stopConversation($context);

            // Track analytics
            $this->mixpanelService->trackEvent('Conversation action', [
                'action' => 'stop',
                'distinct_id' => $user->id,
            ]);

            Log::info('Stop command executed', [
                'user_id' => $user->id,
                'telegram_id' => $user->telegram_id,
            ]);

        } catch (\Exception $e) {
            Log::error('StopCommand error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $context->getUserId(),
            ]);

            $context->sendMessage('❌ An error occurred. Please try again.');
        }
    }
}
