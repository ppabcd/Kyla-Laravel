<?php

namespace App\Listeners;

use App\Application\Services\MatchingService;
use App\Application\Services\UserService;
use App\Domain\Entities\User;
use App\Infrastructure\Repositories\ConversationLogRepository;
use Illuminate\Support\Facades\Log;

class MessageListener
{
    private MatchingService $matchingService;
    private UserService $userService;
    private ConversationLogRepository $conversationLogRepository;

    public function __construct(
        MatchingService $matchingService,
        UserService $userService,
        ConversationLogRepository $conversationLogRepository
    ) {
        $this->matchingService = $matchingService;
        $this->userService = $userService;
        $this->conversationLogRepository = $conversationLogRepository;
    }

    /**
     * Handle incoming text messages
     */
    public function handleTextMessage(User $user, array $context): array
    {
        try {
            // Check if user is in active conversation
            if (!$user->isInConversation()) {
                return $this->handleNoConversation($user);
            }

            // Get partner information
            $partner = $this->matchingService->getConversationPartner($user);
            if (!$partner) {
                return $this->handlePartnerNotFound($user);
            }

            // Check if partner is still active
            if ($partner->isBanned() || !$partner->isActive()) {
                return $this->handleInactivePartner($user, $partner);
            }

            // Check rate limiting
            if ($this->isRateLimited($user)) {
                return [
                    'chat_id' => $user->telegram_id,
                    'text' => __('messages.conversation.locked', [], $user->language ?? 'en'),
                ];
            }

            // Forward message to partner
            $messageText = $context['message']['text'] ?? '';
            $forwardResult = $this->forwardMessageToPartner($user, $partner, $messageText);

            // Log the conversation
            $this->logConversationMessage($user, $partner, $messageText);

            // Update user activity
            $this->userService->updateLastActivity($user->id);

            // Mark user as no longer new if this is their first message
            if ($user->is_new_user) {
                $this->userService->updateUser($user->id, ['is_new_user' => false]);
            }

            return $forwardResult;

        } catch (\Exception $e) {
            Log::error('MessageListener failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'chat_id' => $user->telegram_id,
                'text' => 'An error occurred while sending your message. Please try again.',
            ];
        }
    }

    /**
     * Handle media messages (photos, videos, etc.)
     */
    public function handleMediaMessage(User $user, array $context): array
    {
        try {
            // Check if user is in active conversation
            if (!$user->isInConversation()) {
                return $this->handleNoConversation($user);
            }

            // Get partner information
            $partner = $this->matchingService->getConversationPartner($user);
            if (!$partner) {
                return $this->handlePartnerNotFound($user);
            }

            // Check if partner has safe mode enabled
            if ($partner->safe_mode_enabled) {
                return [
                    'chat_id' => $user->telegram_id,
                    'text' => __('messages.safe_mode.restricted', [], $user->language ?? 'en'),
                ];
            }

            // Check during Ramadan restrictions (if applicable)
            if ($this->isRamadanRestricted()) {
                return [
                    'chat_id' => $user->telegram_id,
                    'text' => __('messages.ramadhan.notice', [], $user->language ?? 'en'),
                ];
            }

            // Forward media to partner
            return $this->forwardMediaToPartner($user, $partner, $context);

        } catch (\Exception $e) {
            Log::error('MediaMessageListener failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'chat_id' => $user->telegram_id,
                'text' => 'An error occurred while sending your media. Please try again.',
            ];
        }
    }

    private function handleNoConversation(User $user): array
    {
        return [
            'chat_id' => $user->telegram_id,
            'text' => __('messages.conversation.not_exists', [], $user->language ?? 'en'),
            'reply_markup' => [
                'inline_keyboard' => [
                    [
                        ['text' => '🔍 Search', 'callback_data' => 'start_search']
                    ]
                ]
            ]
        ];
    }

    private function handlePartnerNotFound(User $user): array
    {
        // End the conversation since partner is not found
        $this->matchingService->endConversation($user);

        return [
            'chat_id' => $user->telegram_id,
            'text' => __('messages.pair.deleted', [], $user->language ?? 'en'),
        ];
    }

    private function handleInactivePartner(User $user, User $partner): array
    {
        // End the conversation and notify user
        $this->matchingService->endConversation($user);

        $message = $partner->isBanned()
            ? __('messages.match.blocked', [], $user->language ?? 'en')
            : __('messages.pair.deleted', [], $user->language ?? 'en');

        return [
            'chat_id' => $user->telegram_id,
            'text' => $message,
        ];
    }

    private function isRateLimited(User $user): bool
    {
        // Check if user is sending messages too quickly
        $lastMessageTime = $user->last_message_at;
        if (!$lastMessageTime) {
            return false;
        }

        $timeDiff = now()->diffInSeconds($lastMessageTime);
        return $timeDiff < 2; // 2 second rate limit
    }

    private function forwardMessageToPartner(User $sender, User $partner, string $message): array
    {
        // Update sender's last message time
        $this->userService->updateUser($sender->id, [
            'last_message_at' => now()
        ]);

        return [
            'chat_id' => $partner->telegram_id,
            'text' => $message,
        ];
    }

    private function forwardMediaToPartner(User $sender, User $partner, array $context): array
    {
        $message = $context['message'];
        $forwardData = [
            'chat_id' => $partner->telegram_id,
        ];

        // Handle different media types
        if (isset($message['photo'])) {
            $forwardData['photo'] = end($message['photo'])['file_id'];
            if (isset($message['caption'])) {
                $forwardData['caption'] = $message['caption'];
            }
        } elseif (isset($message['video'])) {
            $forwardData['video'] = $message['video']['file_id'];
            if (isset($message['caption'])) {
                $forwardData['caption'] = $message['caption'];
            }
        } elseif (isset($message['voice'])) {
            $forwardData['voice'] = $message['voice']['file_id'];
        } elseif (isset($message['sticker'])) {
            $forwardData['sticker'] = $message['sticker']['file_id'];
        }

        return $forwardData;
    }

    private function logConversationMessage(User $sender, User $partner, string $message): void
    {
        try {
            $conversationId = $this->matchingService->getConversationId($sender, $partner);

            $this->conversationLogRepository->create([
                'conversation_id' => $conversationId,
                'user_id' => $sender->id,
                'partner_id' => $partner->id,
                'message' => $message,
                'message_type' => 'text',
                'sent_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log conversation message', [
                'sender_id' => $sender->id,
                'partner_id' => $partner->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function isRamadanRestricted(): bool
    {
        // This could be configurable or based on calendar
        // For now, return false - implement Ramadan detection logic as needed
        return false;
    }
}
