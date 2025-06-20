<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\ConversationLog;
use Illuminate\Database\Eloquent\Collection;

/**
 * Conversation Log Repository Interface
 * 
 * Defines contract for conversation log data access operations
 */
interface ConversationLogRepositoryInterface
{
    /**
     * Basic CRUD Operations
     */
    public function findById(int $id): ?ConversationLog;
    public function create(array $data): ConversationLog;
    public function update(ConversationLog $log, array $data): bool;
    public function delete(ConversationLog $log): bool;
    public function upsert(array $data): ConversationLog;

    /**
     * Query Operations
     */
    public function findByConversationId(string $conversationId): Collection;
    public function findByUserId(int $userId): Collection;
    public function findByMessageId(int $messageId): ?ConversationLog;
    public function findByChatId(int $chatId): Collection;

    /**
     * Conversation Management
     */
    public function findActiveConversations(): Collection;
    public function findConversationByParticipants(int $userId1, int $userId2): Collection;
    public function markAsAction(ConversationLog $log): bool;

    /**
     * Statistics Operations
     */
    public function countMessagesByUser(int $userId): int;
    public function countConversationsByUser(int $userId): int;
    public function findMostActiveUsers(int $limit = 10): Collection;

    /**
     * Cleanup Operations
     */
    public function deleteOldLogs(int $daysOld): int;
    public function findLogsForCleanup(int $daysOld): Collection;
}
