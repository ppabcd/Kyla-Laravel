<?php

namespace App\Telegram\Commands;

use App\Telegram\Core\BaseCommand;
use App\Telegram\Contracts\CommandInterface;
use App\Telegram\Core\TelegramContext;
use App\Application\Services\UserService;
use App\Domain\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Log;

class FeedbackCommand extends BaseCommand implements CommandInterface
{
    protected string|array $commandName = 'feedback';

    public function __construct(
        private UserService $userService,
        private UserRepositoryInterface $userRepository
    ) {}

    public function handle(\App\Telegram\Contracts\TelegramContextInterface $context): void
    {
        try {
            $telegramUser = $context->getFrom();
            if (!$telegramUser) {
                $context->reply('❌ Unable to identify user');
                return;
            }

            $messageText = $context->getMessage()['text'] ?? '';
            $feedbackText = $this->extractFeedbackText($messageText);
            
            if (empty($feedbackText)) {
                $this->startFeedbackConversation($context);
                return;
            }

            $this->processFeedback($context, $telegramUser, $feedbackText);

        } catch (\Exception $e) {
            Log::error('Error in FeedbackCommand', [
                'error' => $e->getMessage(),
                'user_id' => $telegramUser['id'] ?? null
            ]);
            
            $context->reply('❌ An error occurred. Please try again later.');
        }
    }

    private function extractFeedbackText(string $messageText): string
    {
        // Remove /feedback command from the message
        $feedbackText = preg_replace('/^\/feedback\s*/', '', $messageText);
        return trim($feedbackText);
    }

    private function startFeedbackConversation(TelegramContext $context): void
    {
        $message = "📝 **Send Feedback**\n\n";
        $message .= "We'd love to hear your thoughts about Kyla Bot!\n\n";
        $message .= "Please share your feedback, suggestions, or report any issues you've encountered.\n\n";
        $message .= "Your feedback helps us improve the bot for everyone! 🙏";

        $context->reply($message, ['parse_mode' => 'Markdown']);
        
        // Note: In a real implementation, you would set conversation state
        // to wait for the user's feedback text
    }

    private function processFeedback(TelegramContext $context, array $telegramUser, string $feedbackText): void
    {
        $userName = $telegramUser['username'] ?? $telegramUser['first_name'] ?? 'Unknown';
        $userId = $telegramUser['id'];
        
        // Log feedback
        Log::info('Feedback received', [
            'user_id' => $userId,
            'username' => $userName,
            'feedback' => $feedbackText
        ]);

        // Send feedback to admin channel (if configured)
        $adminChannel = config('telegram.admin_channel');
        if ($adminChannel) {
            $adminMessage = "📝 **Feedback Received**\n\n";
            $adminMessage .= "**From:** @{$userName} (ID: {$userId})\n";
            $adminMessage .= "**Feedback:**\n{$feedbackText}";
            
            // Note: In a real implementation, you would send this to the admin channel
            // For now, we'll just log it
            Log::info('Admin notification', [
                'channel' => $adminChannel,
                'message' => $adminMessage
            ]);
        }

        // Thank the user
        $response = "✅ **Thank you for your feedback!**\n\n";
        $response .= "We've received your message and will review it carefully.\n";
        $response .= "Your input helps us make Kyla Bot better for everyone! 🙏\n\n";
        $response .= "If you have any urgent issues, please contact: support@kyla.my.id";

        $context->reply($response, ['parse_mode' => 'Markdown']);
    }
} 
