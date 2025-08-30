<?php

namespace App\Listeners;

use App\Application\Services\MatchingService;
use App\Application\Services\UserService;
use App\Domain\Entities\User;
use App\Infrastructure\Repositories\ConversationLogRepository;
use App\Telegram\Services\KeyboardService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MessageListener
{
    private MatchingService $matchingService;

    private UserService $userService;

    private ConversationLogRepository $conversationLogRepository;

    private KeyboardService $keyboardService;

    public function __construct(
        MatchingService $matchingService,
        UserService $userService,
        ConversationLogRepository $conversationLogRepository,
        KeyboardService $keyboardService
    ) {
        $this->matchingService = $matchingService;
        $this->userService = $userService;
        $this->conversationLogRepository = $conversationLogRepository;
        $this->keyboardService = $keyboardService;
    }

    /**
     * Handle incoming text messages
     */
    public function handleTextMessage(User $user, array $context): array
    {
        try {
            // Validate input
            if (! $user || ! $user->id) {
                Log::warning('Invalid user provided to MessageListener');

                return [
                    'chat_id' => $context['message']['chat']['id'] ?? null,
                    'text' => '❌ User validation failed. Please try again.',
                ];
            }

            if (! isset($context['message']['text'])) {
                Log::warning('No text found in message context');

                return [];
            }

            // Check if user is in active conversation
            if (! $user->isInConversation()) {
                return $this->handleNoConversation($user);
            }

            // Get partner information
            $partner = $this->matchingService->getConversationPartner($user);
            if (! $partner) {
                return $this->handlePartnerNotFound($user);
            }

            // Check if partner is banned
            if ($partner->isBanned()) {
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

            if (empty(trim($messageText))) {
                Log::debug('Empty message text, skipping forward');

                return [];
            }

            $forwardResult = $this->forwardMessageToPartner($user, $partner, $messageText);

            // Log the conversation
            $this->logConversationMessage($user, $partner, $messageText);

            // Update user activity
            try {
                $this->userService->updateLastActivity($user->id);
            } catch (\Exception $e) {
                Log::error('Failed to update user activity', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }

            // Mark user as no longer new if this is their first message
            if (isset($user->is_new_user) && $user->is_new_user) {
                try {
                    $this->userService->updateUser($user->id, ['is_new_user' => false]);
                } catch (\Exception $e) {
                    Log::error('Failed to update new user status', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return $forwardResult;

        } catch (\Exception $e) {
            Log::error('MessageListener failed', [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'context_keys' => array_keys($context),
            ]);

            return [
                'chat_id' => $user->telegram_id ?? $context['message']['chat']['id'] ?? null,
                'text' => __('messages.error.general', [], $user->language ?? 'en') ?: 'An error occurred while sending your message. Please try again.',
            ];
        }
    }

    /**
     * Handle media messages (photos, videos, etc.)
     */
    public function handleMediaMessage(User $user, array $context): array
    {
        try {
            // Validate input
            if (! $user || ! $user->id) {
                Log::warning('Invalid user provided to MediaMessageListener');

                return [
                    'chat_id' => $context['message']['chat']['id'] ?? null,
                    'text' => '❌ User validation failed. Please try again.',
                ];
            }

            if (! isset($context['message']) || ! is_array($context['message'])) {
                Log::warning('No valid message found in media context');

                return [];
            }

            // Check if user is in active conversation
            if (! $user->isInConversation()) {
                return $this->handleNoConversation($user);
            }

            // Get partner information
            $partner = $this->matchingService->getConversationPartner($user);
            if (! $partner) {
                return $this->handlePartnerNotFound($user);
            }

            // Check if partner has safe mode enabled, unless temporary media is enabled for this conversation
            $tempMediaEnabled = (bool) Cache::get("enable-media:{$partner->id}", false);
            if ((isset($partner->safe_mode) && $partner->safe_mode) && ! $tempMediaEnabled) {
                return [
                    'chat_id' => $user->telegram_id,
                    'text' => __('messages.safe_mode.restricted', [], $user->language ?? 'en'),
                    'reply_markup' => $this->keyboardService->getSafeModeKeyboard(),
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
            $forwardResult = $this->forwardMediaToPartner($user, $partner, $context);

            // Update user activity
            try {
                $this->userService->updateLastActivity($user->id);
            } catch (\Exception $e) {
                Log::error('Failed to update user activity for media message', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }

            return $forwardResult;

        } catch (\Exception $e) {
            Log::error('MediaMessageListener failed', [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'context_keys' => array_keys($context),
            ]);

            return [
                'chat_id' => $user->telegram_id ?? $context['message']['chat']['id'] ?? null,
                'text' => __('messages.error.media', [], $user->language ?? 'en') ?: 'An error occurred while sending your media. Please try again.',
            ];
        }
    }

    private function handleNoConversation(User $user): array
    {
        return [
            'chat_id' => $user->telegram_id,
            'text' => __('messages.conversation.not_exists', [], $user->language ?? 'en'),
            'reply_markup' => $this->keyboardService->getSearchKeyboard(),
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
        if (! $lastMessageTime) {
            return false;
        }

        $timeDiff = now()->diffInSeconds($lastMessageTime);

        return $timeDiff < 2; // 2 second rate limit
    }

    private function forwardMessageToPartner(User $sender, User $partner, string $message): array
    {
        // Update sender's last message time
        $this->userService->updateUser($sender->id, [
            'last_message_at' => now(),
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
        } elseif (isset($message['document'])) {
            $forwardData['document'] = $message['document']['file_id'];
            if (isset($message['caption'])) {
                $forwardData['caption'] = $message['caption'];
            }
        } elseif (isset($message['audio'])) {
            $forwardData['audio'] = $message['audio']['file_id'];
            if (isset($message['caption'])) {
                $forwardData['caption'] = $message['caption'];
            }
        } elseif (isset($message['voice'])) {
            $forwardData['voice'] = $message['voice']['file_id'];
        } elseif (isset($message['sticker'])) {
            $forwardData['sticker'] = $message['sticker']['file_id'];
        } elseif (isset($message['animation'])) {
            $forwardData['animation'] = $message['animation']['file_id'];
            if (isset($message['caption'])) {
                $forwardData['caption'] = $message['caption'];
            }
        } else {
            // Fallback for unknown media types
            Log::warning('Unknown media type in forward', [
                'sender_id' => $sender->id,
                'partner_id' => $partner->id,
                'message_keys' => array_keys($message),
            ]);

            return [
                'chat_id' => $partner->telegram_id,
                'text' => '📎 Media content (unsupported format)',
            ];
        }

        // Update sender's last message time
        try {
            $this->userService->updateUser($sender->id, [
                'last_message_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update sender last message time', [
                'sender_id' => $sender->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $forwardData;
    }

    private function logConversationMessage(User $sender, User $partner, string $message): void
    {
        try {
            $conversationId = $this->matchingService->getConversationId($sender, $partner);

            $this->conversationLogRepository->create([
                'conv_id' => $conversationId,
                'user_id' => $sender->id,
                'chat_id' => $sender->telegram_id,
                'message_id' => time() + $sender->id, // Generate a unique message_id
                'is_action' => 0,
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
