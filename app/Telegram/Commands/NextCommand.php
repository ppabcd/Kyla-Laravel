<?php

namespace App\Telegram\Commands;

use App\Telegram\Core\BaseCommand;
use App\Telegram\Contracts\TelegramContextInterface;
use App\Services\ConversationService;
use App\Services\MixpanelService;
use App\Telegram\Middleware\CheckGenderMiddleware;
use App\Telegram\Middleware\CheckInterestMiddleware;
use App\Telegram\Middleware\CheckBannedUserMiddleware;
use App\Telegram\Middleware\CheckCaptchaMiddleware;
use App\Telegram\Middleware\CheckPromotionMiddleware;
use App\Telegram\Middleware\CheckAnnouncementMiddleware;
use Illuminate\Support\Facades\Log;

class NextCommand extends BaseCommand
{
    protected string|array $commandName = 'next';
    protected string $description = 'End current conversation and search for next partner';
    protected string $usage = '/next';

    // Middleware handled by TelegramBotService

    public function __construct(
        private ConversationService $conversationService,
        private MixpanelService $mixpanelService
    ) {
    }

    public function handle(TelegramContextInterface $context): void
    {
        try {
            $user = $context->getUserModel();

            if (!$user) {
                $context->sendMessage('❌ User not found');
                return;
            }

            // Call conversation service to handle next conversation
            $this->conversationService->nextConversation($context);

            // Track analytics
            $this->mixpanelService->trackEvent('Conversation action', [
                'action' => 'next',
                'distinct_id' => $user->id
            ]);

            Log::info('Next command executed', [
                'user_id' => $user->id,
                'telegram_id' => $user->telegram_id
            ]);

        } catch (\Exception $e) {
            Log::error('NextCommand error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $context->getUserId()
            ]);

            $context->sendMessage('❌ An error occurred. Please try again.');
        }
    }
}
