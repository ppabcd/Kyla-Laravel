<?php

namespace App\Telegram\Listeners;

use App\Domain\Entities\User;
use App\Domain\Repositories\PairRepositoryInterface;
use App\Infrastructure\Repositories\UserRepository;
use App\Listeners\MessageListener as AppMessageListener;
use App\Telegram\Contracts\TelegramContextInterface;
use App\Telegram\Services\KeyboardService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MessageListener
{
    private AppMessageListener $messageListener;

    private UserRepository $userRepository;

    private PairRepositoryInterface $pairRepository;

    private KeyboardService $keyboardService;

    private string $botToken;

    public function __construct(
        AppMessageListener $messageListener,
        UserRepository $userRepository,
        PairRepositoryInterface $pairRepository,
        KeyboardService $keyboardService
    ) {
        $this->messageListener = $messageListener;
        $this->userRepository = $userRepository;
        $this->pairRepository = $pairRepository;
        $this->keyboardService = $keyboardService;
        $this->botToken = config('telegram.bot_token');
    }

    /**
     * Handle incoming text messages from users
     */
    public function handleTextMessage(TelegramContextInterface $context): void
    {
        try {
            $user = $context->getUserModel();

            // Fallback: resolve user from Telegram payload when middleware did not set it
            if (! $user) {
                $tgUser = $context->getUser();
                $telegramId = $tgUser['id'] ?? null;
                if ($telegramId) {
                    $resolved = $this->userRepository->findByTelegramId($telegramId);
                    if ($resolved) {
                        $context->setUser($resolved);
                        $user = $resolved;
                    }
                }
            }

            if (! $user) {
                Log::warning('No user found in context for text message');

                return;
            }

            $message = $context->getMessage();
            if (! $message || ! isset($message['text'])) {
                return;
            }

            $text = $message['text'];

            // Skip if it's a command (starts with /)
            if (str_starts_with($text, '/')) {
                return;
            }

            // Check if it's a private chat or group
            $chatType = $context->getChatType();

            // Handle private chat messages (conversations)
            if ($chatType === 'private') {
                $this->handlePrivateMessage($user, $context);
            }
            // Handle group messages
            elseif (in_array($chatType, ['group', 'supergroup'])) {
                $this->handleGroupMessage($user, $context);
            }

        } catch (\Exception $e) {
            Log::error('Telegram MessageListener failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'context' => $context->getUpdate(),
            ]);
        }
    }

    /**
     * Handle media messages (photos, videos, documents, etc.)
     */
    public function handleMediaMessage(TelegramContextInterface $context): void
    {
        try {
            $user = $context->getUserModel();

            // Fallback: resolve user from Telegram payload when middleware did not set it
            if (! $user) {
                $tgUser = $context->getUser();
                $telegramId = $tgUser['id'] ?? null;
                if ($telegramId) {
                    $resolved = $this->userRepository->findByTelegramId($telegramId);
                    if ($resolved) {
                        $context->setUser($resolved);
                        $user = $resolved;
                    }
                }
            }

            if (! $user) {
                Log::warning('No user found in context for media message');

                return;
            }

            $message = $context->getMessage();
            if (! $message) {
                return;
            }

            // Check if it's a media message
            $hasMedia = isset($message['photo']) ||
                isset($message['video']) ||
                isset($message['document']) ||
                isset($message['audio']) ||
                isset($message['voice']) ||
                isset($message['sticker']) ||
                isset($message['animation']);

            if (! $hasMedia) {
                return;
            }

            $chatType = $context->getChatType();

            // Handle private chat media (conversations)
            if ($chatType === 'private') {
                $this->handlePrivateMedia($user, $context);
            }
            // Handle group media
            elseif (in_array($chatType, ['group', 'supergroup'])) {
                $this->handleGroupMedia($user, $context);
            }

        } catch (\Exception $e) {
            Log::error('Telegram MediaMessageListener failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'context' => $context->getUpdate(),
            ]);
        }
    }

    /**
     * Handle other message types (location, contact, etc.)
     */
    public function handleOtherMessage(TelegramContextInterface $context): void
    {
        try {
            $user = $context->getUserModel();

            if (! $user) {
                return;
            }

            $message = $context->getMessage();
            if (! $message) {
                return;
            }

            $chatType = $context->getChatType();

            // Handle location sharing
            if (isset($message['location'])) {
                $this->handleLocationMessage($user, $context);
            }
            // Handle contact sharing
            elseif (isset($message['contact'])) {
                $this->handleContactMessage($user, $context);
            }
            // Handle other message types
            else {
                Log::debug('Unhandled message type received', [
                    'user_id' => $user->id,
                    'chat_type' => $chatType,
                    'message_keys' => array_keys($message),
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Telegram OtherMessageListener failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Handle private chat text messages (conversation forwarding)
     */
    private function handlePrivateMessage(User $user, TelegramContextInterface $context): void
    {
        // Soft notice if partner appears inactive (do not end conversation)
        try {
            $pair = $this->pairRepository->findActivePairByUserId($user->id);
            if ($pair) {
                $partner = $pair->getOtherUser($user->id);
                if ($partner && method_exists($partner, 'isActive') && ! $partner->isActive()) {
                    $noticeKey = 'inactive-notice:'.$pair->id;
                    if (Cache::add($noticeKey, true, now()->addMinutes(10))) {
                        $context->reply(
                            __('messages.pair.inactive', [], $user->language_code ?? 'en'),
                            [
                                'reply_markup' => $this->keyboardService->getNextSearchKeyboard(),
                            ]
                        );
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::debug('Failed sending inactive partner notice', ['error' => $e->getMessage()]);
        }

        $contextArray = $context->getUpdate();
        $response = $this->messageListener->handleTextMessage($user, $contextArray);

        if ($response && isset($response['chat_id'])) {
            $this->sendTelegramMessage($response);
        }
    }

    /**
     * Handle private chat media messages (conversation forwarding)
     */
    private function handlePrivateMedia(User $user, TelegramContextInterface $context): void
    {
        $contextArray = $context->getUpdate();
        $response = $this->messageListener->handleMediaMessage($user, $contextArray);

        if ($response && isset($response['chat_id'])) {
            $this->sendTelegramMessage($response);
        }
    }

    /**
     * Handle group text messages
     */
    private function handleGroupMessage(User $user, TelegramContextInterface $context): void
    {
        $message = $context->getMessage();
        $text = $message['text'] ?? '';
        $chatId = $context->getChatId();
        $groupTitle = $context->getChat()['title'] ?? 'Unknown Group';

        Log::info('Group message received', [
            'user_id' => $user->id,
            'username' => $user->username,
            'group_id' => $chatId,
            'group_title' => $groupTitle,
            'message' => $text,
        ]);

        // Implementasi khusus untuk pesan grup bisa ditambahkan di sini
        // Misalnya: moderation, bot mentions, group commands, etc.

        // Check if bot is mentioned
        if ($this->isBotMentioned($text)) {
            $this->handleBotMention($user, $context);
        }
    }

    /**
     * Handle group media messages
     */
    private function handleGroupMedia(User $user, TelegramContextInterface $context): void
    {
        $message = $context->getMessage();
        $chatId = $context->getChatId();
        $groupTitle = $context->getChat()['title'] ?? 'Unknown Group';

        $mediaType = $this->getMediaType($message);

        Log::info('Group media received', [
            'user_id' => $user->id,
            'username' => $user->username,
            'group_id' => $chatId,
            'group_title' => $groupTitle,
            'media_type' => $mediaType,
        ]);

        // Implementasi khusus untuk media grup bisa ditambahkan di sini
        // Misalnya: content moderation, file analysis, etc.
    }

    /**
     * Handle location messages
     */
    private function handleLocationMessage(User $user, TelegramContextInterface $context): void
    {
        $message = $context->getMessage();
        $location = $message['location'];

        Log::info('Location shared', [
            'user_id' => $user->id,
            'latitude' => $location['latitude'],
            'longitude' => $location['longitude'],
        ]);

        // Bisa digunakan untuk fitur location-based matching
        // atau untuk menyimpan lokasi user
    }

    /**
     * Handle contact messages
     */
    private function handleContactMessage(User $user, TelegramContextInterface $context): void
    {
        $message = $context->getMessage();
        $contact = $message['contact'];

        Log::info('Contact shared', [
            'user_id' => $user->id,
            'contact_user_id' => $contact['user_id'] ?? null,
            'phone_number' => $contact['phone_number'] ?? null,
        ]);

        // Implementasi untuk contact sharing
    }

    /**
     * Check if bot is mentioned in the message
     */
    private function isBotMentioned(string $text): bool
    {
        $botUsername = config('telegram.bot_username');

        if (! $botUsername) {
            return false;
        }

        return str_contains(strtolower($text), '@'.strtolower($botUsername));
    }

    /**
     * Handle when bot is mentioned in group
     */
    private function handleBotMention(User $user, TelegramContextInterface $context): void
    {
        $context->reply(
            "👋 Halo! Saya adalah bot untuk chat anonymous. Silakan chat saya secara private untuk memulai conversation.\n\n".
            'Ketik /start untuk memulai!'
        );
    }

    /**
     * Get media type from message
     */
    private function getMediaType(array $message): string
    {
        if (isset($message['photo'])) {
            return 'photo';
        }
        if (isset($message['video'])) {
            return 'video';
        }
        if (isset($message['document'])) {
            return 'document';
        }
        if (isset($message['audio'])) {
            return 'audio';
        }
        if (isset($message['voice'])) {
            return 'voice';
        }
        if (isset($message['sticker'])) {
            return 'sticker';
        }
        if (isset($message['animation'])) {
            return 'animation';
        }

        return 'unknown';
    }

    /**
     * Send telegram message via HTTP API
     */
    private function sendTelegramMessage(array $messageData): void
    {
        try {
            $method = 'sendMessage';

            // Determine API method based on message content
            if (isset($messageData['photo'])) {
                $method = 'sendPhoto';
            } elseif (isset($messageData['video'])) {
                $method = 'sendVideo';
            } elseif (isset($messageData['document'])) {
                $method = 'sendDocument';
            } elseif (isset($messageData['audio'])) {
                $method = 'sendAudio';
            } elseif (isset($messageData['voice'])) {
                $method = 'sendVoice';
            } elseif (isset($messageData['sticker'])) {
                $method = 'sendSticker';
            }

            $url = "https://api.telegram.org/bot{$this->botToken}/{$method}";

            // Telegram expects reply_markup as JSON string; encode if array provided
            if (isset($messageData['reply_markup']) && is_array($messageData['reply_markup'])) {
                $messageData['reply_markup'] = json_encode($messageData['reply_markup']);
            }

            $response = Http::post($url, $messageData);

            if (! $response->successful()) {
                Log::error('Failed to send Telegram message', [
                    'method' => $method,
                    'data' => $messageData,
                    'response' => $response->json(),
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error sending Telegram message', [
                'error' => $e->getMessage(),
                'data' => $messageData,
            ]);
        }
    }
}
