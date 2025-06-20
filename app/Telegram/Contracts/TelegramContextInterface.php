<?php

namespace App\Telegram\Contracts;

interface TelegramContextInterface
{
    /**
     * Get the update data
     */
    public function getUpdate(): array;

    /**
     * Get message data
     */
    public function getMessage(): ?array;

    /**
     * Get callback query data
     */
    public function getCallbackQuery(): ?array;

    /**
     * Get chat data
     */
    public function getChat(): ?array;

    /**
     * Get user data
     */
    public function getUser(): ?array;

    /**
     * Get chat type
     */
    public function getChatType(): ?string;

    /**
     * Check if it's a private chat
     */
    public function isPrivate(): bool;

    /**
     * Check if it's a group chat
     */
    public function isGroup(): bool;

    /**
     * Get text from message
     */
    public function getText(): ?string;

    /**
     * Get callback data
     */
    public function getCallbackData(): ?string;

    /**
     * Get message ID
     */
    public function getMessageId(): ?int;

    /**
     * Get chat ID
     */
    public function getChatId(): ?int;

    /**
     * Get user ID
     */
    public function getUserId(): ?int;

    /**
     * Send text message
     */
    public function sendMessage(string $text, array $options = []): array;

    /**
     * Reply to message
     */
    public function reply(string $text, array $options = []): array;

    /**
     * Set user model
     */
    public function setUser($user): void;

    /**
     * Get user model
     */
    public function getUserModel();

    /**
     * Send keyboard
     */
    public function sendKeyboard(string $text, array $keyboard, array $options = []): array;

    /**
     * Send inline keyboard
     */
    public function sendInlineKeyboard(string $text, array $keyboard, array $options = []): array;

    /**
     * Delete message
     */
    public function deleteMessage(?int $messageId = null): array;

    /**
     * Answer callback query
     */
    public function answerCallbackQuery(string $text = '', array $options = []): array;

    /**
     * Edit message text
     */
    public function editMessageText(string $text, array $options = []): array;

    /**
     * Edit message reply markup
     */
    public function editMessageReplyMarkup(array $replyMarkup, array $options = []): array;

    /**
     * Send photo
     */
    public function sendPhoto(string $photo, array $options = []): array;

    /**
     * Send document
     */
    public function sendDocument(string $document, array $options = []): array;

    /**
     * Send video
     */
    public function sendVideo(string $video, array $options = []): array;

    /**
     * Send audio
     */
    public function sendAudio(string $audio, array $options = []): array;

    /**
     * Send voice
     */
    public function sendVoice(string $voice, array $options = []): array;

    /**
     * Send location
     */
    public function sendLocation(float $latitude, float $longitude, array $options = []): array;

    /**
     * Send venue
     */
    public function sendVenue(float $latitude, float $longitude, string $title, string $address, array $options = []): array;

    /**
     * Send contact
     */
    public function sendContact(string $phoneNumber, string $firstName, array $options = []): array;

    /**
     * Send sticker
     */
    public function sendSticker(string $sticker, array $options = []): array;

    /**
     * Send animation
     */
    public function sendAnimation(string $animation, array $options = []): array;

    /**
     * Send dice
     */
    public function sendDice(array $options = []): array;

    /**
     * Send poll
     */
    public function sendPoll(string $question, array $options, array $pollOptions = []): array;

    /**
     * Send quiz
     */
    public function sendQuiz(string $question, array $options, int $correctOptionId, array $quizOptions = []): array;

    /**
     * Stop poll
     */
    public function stopPoll(int $messageId, array $options = []): array;

    /**
     * Pin chat message
     */
    public function pinChatMessage(int $messageId, array $options = []): array;

    /**
     * Unpin chat message
     */
    public function unpinChatMessage(int $messageId, array $options = []): array;

    /**
     * Unpin all chat messages
     */
    public function unpinAllChatMessages(array $options = []): array;

    /**
     * Leave chat
     */
    public function leaveChat(array $options = []): array;

    /**
     * Get chat
     */
    public function getChatInfo(array $options = []): array;

    /**
     * Get chat administrators
     */
    public function getChatAdministrators(array $options = []): array;

    /**
     * Get chat member count
     */
    public function getChatMemberCount(array $options = []): array;

    /**
     * Get chat member
     */
    public function getChatMember(int $userId, array $options = []): array;

    /**
     * Set chat sticker set
     */
    public function setChatStickerSet(string $stickerSetName, array $options = []): array;

    /**
     * Delete chat sticker set
     */
    public function deleteChatStickerSet(array $options = []): array;

    /**
     * Get file
     */
    public function getFile(string $fileId): array;

    /**
     * Get user profile photos
     */
    public function getUserProfilePhotos(int $userId, array $options = []): array;

    /**
     * Get updates
     */
    public function getUpdates(array $options = []): array;

    /**
     * Set webhook
     */
    public function setWebhook(string $url, array $options = []): array;

    /**
     * Delete webhook
     */
    public function deleteWebhook(array $options = []): array;

    /**
     * Get webhook info
     */
    public function getWebhookInfo(): array;

    /**
     * Get bot info
     */
    public function getMe(): array;

    /**
     * Log out
     */
    public function logOut(): array;

    /**
     * Close
     */
    public function close(): array;
} 
