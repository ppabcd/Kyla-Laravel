<?php

namespace App\Telegram\Core;

use App\Telegram\Contracts\TelegramContextInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TelegramContext implements TelegramContextInterface
{
    protected array $update;

    protected string $botToken;

    protected $userModel = null;

    public function __construct(array $update, string $botToken)
    {
        $this->update = $update;
        $this->botToken = $botToken;
    }

    public function getUpdate(): array
    {
        return $this->update;
    }

    public function getMessage(): ?array
    {
        return $this->update['message'] ?? null;
    }

    public function getCallbackQuery(): ?array
    {
        return $this->update['callback_query'] ?? null;
    }

    public function getChat(): ?array
    {
        if ($message = $this->getMessage()) {
            return $message['chat'] ?? null;
        }

        if ($callbackQuery = $this->getCallbackQuery()) {
            return $callbackQuery['message']['chat'] ?? null;
        }

        return null;
    }

    public function getUser(): ?array
    {
        if ($message = $this->getMessage()) {
            return $message['from'] ?? null;
        }

        if ($callbackQuery = $this->getCallbackQuery()) {
            return $callbackQuery['from'] ?? null;
        }

        return null;
    }

    // getFrom() deprecated; use getUser() instead

    public function setUser($user): void
    {
        $this->userModel = $user;
    }

    public function getUserModel()
    {
        return $this->userModel;
    }

    public function getChatType(): ?string
    {
        $chat = $this->getChat();

        return $chat['type'] ?? null;
    }

    public function isPrivate(): bool
    {
        return $this->getChatType() === 'private';
    }

    public function isGroup(): bool
    {
        $chatType = $this->getChatType();

        return $chatType === 'group' || $chatType === 'supergroup';
    }

    public function getText(): ?string
    {
        $message = $this->getMessage();

        return $message['text'] ?? null;
    }

    public function getCallbackData(): ?string
    {
        $callbackQuery = $this->getCallbackQuery();

        return $callbackQuery['data'] ?? null;
    }

    public function getMessageId(): ?int
    {
        if ($message = $this->getMessage()) {
            return $message['message_id'] ?? null;
        }

        if ($callbackQuery = $this->getCallbackQuery()) {
            return $callbackQuery['message']['message_id'] ?? null;
        }

        return null;
    }

    public function getChatId(): ?int
    {
        $chat = $this->getChat();

        return $chat['id'] ?? null;
    }

    public function getUserId(): ?int
    {
        $user = $this->getUser();

        return $user['id'] ?? null;
    }

    public function sendMessage(string $text, array $options = []): array
    {
        $text = Str::replace('\n', "\n", $text);
        $data = array_merge([
            'chat_id' => $this->getChatId(),
            'text' => $text,
        ], $options);

        return $this->makeRequest('sendMessage', $data);
    }

    public function reply(string $text, array $options = []): array
    {
        $data = array_merge([
            'chat_id' => $this->getChatId(),
            'text' => $text,
            'reply_to_message_id' => $this->getMessageId(),
        ], $options);

        return $this->makeRequest('sendMessage', $data);
    }

    public function sendKeyboard(string $text, array $keyboard, array $options = []): array
    {
        $data = array_merge([
            'chat_id' => $this->getChatId(),
            'text' => $text,
            'reply_markup' => json_encode([
                'keyboard' => $keyboard,
                'resize_keyboard' => true,
                'one_time_keyboard' => false,
                'selective' => false,
            ]),
        ], $options);

        return $this->makeRequest('sendMessage', $data);
    }

    public function sendInlineKeyboard(string $text, array $keyboard, array $options = []): array
    {
        $data = array_merge([
            'chat_id' => $this->getChatId(),
            'text' => $text,
            'reply_markup' => json_encode([
                'inline_keyboard' => $keyboard,
            ]),
        ], $options);

        return $this->makeRequest('sendMessage', $data);
    }

    public function deleteMessage(?int $messageId = null): array
    {
        $data = [
            'chat_id' => $this->getChatId(),
            'message_id' => $messageId ?? $this->getMessageId(),
        ];

        return $this->makeRequest('deleteMessage', $data);
    }

    public function answerCallbackQuery(string $text = '', array $options = []): array
    {
        $callbackQuery = $this->getCallbackQuery();
        if (! $callbackQuery) {
            throw new \Exception('No callback query found');
        }

        $data = array_merge([
            'callback_query_id' => $callbackQuery['id'],
            'text' => $text,
        ], $options);

        return $this->makeRequest('answerCallbackQuery', $data);
    }

    public function editMessageText(string $text, array $options = []): array
    {
        $data = array_merge([
            'chat_id' => $this->getChatId(),
            'message_id' => $this->getMessageId(),
            'text' => $text,
        ], $options);

        return $this->makeRequest('editMessageText', $data);
    }

    public function editMessageReplyMarkup(array $replyMarkup, array $options = []): array
    {
        $data = array_merge([
            'chat_id' => $this->getChatId(),
            'message_id' => $this->getMessageId(),
            'reply_markup' => json_encode($replyMarkup),
        ], $options);

        return $this->makeRequest('editMessageReplyMarkup', $data);
    }

    public function sendPhoto(string $photo, array $options = []): array
    {
        $data = array_merge([
            'chat_id' => $this->getChatId(),
            'photo' => $photo,
        ], $options);

        return $this->makeRequest('sendPhoto', $data);
    }

    public function sendDocument(string $document, array $options = []): array
    {
        $data = array_merge([
            'chat_id' => $this->getChatId(),
            'document' => $document,
        ], $options);

        return $this->makeRequest('sendDocument', $data);
    }

    public function sendVideo(string $video, array $options = []): array
    {
        $data = array_merge([
            'chat_id' => $this->getChatId(),
            'video' => $video,
        ], $options);

        return $this->makeRequest('sendVideo', $data);
    }

    public function sendAudio(string $audio, array $options = []): array
    {
        $data = array_merge([
            'chat_id' => $this->getChatId(),
            'audio' => $audio,
        ], $options);

        return $this->makeRequest('sendAudio', $data);
    }

    public function sendVoice(string $voice, array $options = []): array
    {
        $data = array_merge([
            'chat_id' => $this->getChatId(),
            'voice' => $voice,
        ], $options);

        return $this->makeRequest('sendVoice', $data);
    }

    public function sendLocation(float $latitude, float $longitude, array $options = []): array
    {
        $data = array_merge([
            'chat_id' => $this->getChatId(),
            'latitude' => $latitude,
            'longitude' => $longitude,
        ], $options);

        return $this->makeRequest('sendLocation', $data);
    }

    public function sendVenue(float $latitude, float $longitude, string $title, string $address, array $options = []): array
    {
        $data = array_merge([
            'chat_id' => $this->getChatId(),
            'latitude' => $latitude,
            'longitude' => $longitude,
            'title' => $title,
            'address' => $address,
        ], $options);

        return $this->makeRequest('sendVenue', $data);
    }

    public function sendContact(string $phoneNumber, string $firstName, array $options = []): array
    {
        $data = array_merge([
            'chat_id' => $this->getChatId(),
            'phone_number' => $phoneNumber,
            'first_name' => $firstName,
        ], $options);

        return $this->makeRequest('sendContact', $data);
    }

    public function sendSticker(string $sticker, array $options = []): array
    {
        $data = array_merge([
            'chat_id' => $this->getChatId(),
            'sticker' => $sticker,
        ], $options);

        return $this->makeRequest('sendSticker', $data);
    }

    public function sendAnimation(string $animation, array $options = []): array
    {
        $data = array_merge([
            'chat_id' => $this->getChatId(),
            'animation' => $animation,
        ], $options);

        return $this->makeRequest('sendAnimation', $data);
    }

    public function sendDice(array $options = []): array
    {
        $data = array_merge([
            'chat_id' => $this->getChatId(),
        ], $options);

        return $this->makeRequest('sendDice', $data);
    }

    public function sendPoll(string $question, array $options, array $pollOptions = []): array
    {
        $data = array_merge([
            'chat_id' => $this->getChatId(),
            'question' => $question,
            'options' => json_encode($options),
        ], $pollOptions);

        return $this->makeRequest('sendPoll', $data);
    }

    public function sendQuiz(string $question, array $options, int $correctOptionId, array $quizOptions = []): array
    {
        $data = array_merge([
            'chat_id' => $this->getChatId(),
            'question' => $question,
            'options' => json_encode($options),
            'correct_option_id' => $correctOptionId,
            'is_anonymous' => false,
            'type' => 'quiz',
        ], $quizOptions);

        return $this->makeRequest('sendPoll', $data);
    }

    public function stopPoll(int $messageId, array $options = []): array
    {
        $data = array_merge([
            'chat_id' => $this->getChatId(),
            'message_id' => $messageId,
        ], $options);

        return $this->makeRequest('stopPoll', $data);
    }

    public function pinChatMessage(int $messageId, array $options = []): array
    {
        $data = array_merge([
            'chat_id' => $this->getChatId(),
            'message_id' => $messageId,
        ], $options);

        return $this->makeRequest('pinChatMessage', $data);
    }

    public function unpinChatMessage(int $messageId, array $options = []): array
    {
        $data = array_merge([
            'chat_id' => $this->getChatId(),
            'message_id' => $messageId,
        ], $options);

        return $this->makeRequest('unpinChatMessage', $data);
    }

    public function unpinAllChatMessages(array $options = []): array
    {
        $data = array_merge([
            'chat_id' => $this->getChatId(),
        ], $options);

        return $this->makeRequest('unpinAllChatMessages', $data);
    }

    public function leaveChat(array $options = []): array
    {
        $data = array_merge([
            'chat_id' => $this->getChatId(),
        ], $options);

        return $this->makeRequest('leaveChat', $data);
    }

    public function getChatInfo(array $options = []): array
    {
        $data = array_merge([
            'chat_id' => $this->getChatId(),
        ], $options);

        return $this->makeRequest('getChat', $data);
    }

    public function getChatAdministrators(array $options = []): array
    {
        $data = array_merge([
            'chat_id' => $this->getChatId(),
        ], $options);

        return $this->makeRequest('getChatAdministrators', $data);
    }

    public function getChatMemberCount(array $options = []): array
    {
        $data = array_merge([
            'chat_id' => $this->getChatId(),
        ], $options);

        return $this->makeRequest('getChatMemberCount', $data);
    }

    public function getChatMember(int $userId, array $options = []): array
    {
        $data = array_merge([
            'chat_id' => $this->getChatId(),
            'user_id' => $userId,
        ], $options);

        return $this->makeRequest('getChatMember', $data);
    }

    public function setChatStickerSet(string $stickerSetName, array $options = []): array
    {
        $data = array_merge([
            'chat_id' => $this->getChatId(),
            'sticker_set_name' => $stickerSetName,
        ], $options);

        return $this->makeRequest('setChatStickerSet', $data);
    }

    public function deleteChatStickerSet(array $options = []): array
    {
        $data = array_merge([
            'chat_id' => $this->getChatId(),
        ], $options);

        return $this->makeRequest('deleteChatStickerSet', $data);
    }

    public function getFile(string $fileId): array
    {
        $data = [
            'file_id' => $fileId,
        ];

        return $this->makeRequest('getFile', $data);
    }

    public function getUserProfilePhotos(int $userId, array $options = []): array
    {
        $data = array_merge([
            'user_id' => $userId,
        ], $options);

        return $this->makeRequest('getUserProfilePhotos', $data);
    }

    public function getUpdates(array $options = []): array
    {
        return $this->makeRequest('getUpdates', $options);
    }

    public function setWebhook(string $url, array $options = []): array
    {
        $data = array_merge([
            'url' => $url,
        ], $options);

        return $this->makeRequest('setWebhook', $data);
    }

    public function deleteWebhook(array $options = []): array
    {
        return $this->makeRequest('deleteWebhook', $options);
    }

    public function getWebhookInfo(): array
    {
        return $this->makeRequest('getWebhookInfo');
    }

    public function getMe(): array
    {
        return $this->makeRequest('getMe');
    }

    public function logOut(): array
    {
        return $this->makeRequest('logOut');
    }

    public function close(): array
    {
        return $this->makeRequest('close');
    }

    /**
     * Send message to specific chat ID (static method for external use)
     */
    public static function sendMessageToChat(string $chatId, string $text, array $options = []): array
    {
        $botToken = config('telegram.bot_token');
        $data = array_merge([
            'chat_id' => $chatId,
            'text' => $text,
        ], $options);

        return self::makeStaticRequest($botToken, 'sendMessage', $data);
    }

    /**
     * Static method for making Telegram API requests
     */
    protected static function makeStaticRequest(string $botToken, string $method, array $data = []): array
    {
        try {
            $response = Http::post("https://api.telegram.org/bot{$botToken}/{$method}", $data);

            if ($response->successful()) {
                return $response->json();
            } else {
                Log::error('Telegram API request failed (static)', [
                    'method' => $method,
                    'data' => $data,
                    'response' => $response->body(),
                    'status' => $response->status(),
                ]);

                return [
                    'ok' => false,
                    'error_code' => $response->status(),
                    'description' => 'HTTP request failed',
                ];
            }
        } catch (\Exception $e) {
            Log::error('Telegram API request exception (static)', [
                'method' => $method,
                'data' => $data,
                'error' => $e->getMessage(),
            ]);

            return [
                'ok' => false,
                'error_code' => 0,
                'description' => $e->getMessage(),
            ];
        }
    }

    protected function makeRequest(string $method, array $data = []): array
    {
        return self::makeStaticRequest($this->botToken, $method, $data);
    }
}
