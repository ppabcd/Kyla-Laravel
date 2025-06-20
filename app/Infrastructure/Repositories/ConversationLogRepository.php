<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\ConversationLog;
use App\Domain\Repositories\ConversationLogRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Conversation Log Repository Implementation
 * 
 * Infrastructure layer implementation of ConversationLogRepositoryInterface
 */
class ConversationLogRepository implements ConversationLogRepositoryInterface
{
    /**
     * Basic CRUD Operations
     */
    public function findById(int $id): ?ConversationLog
    {
        return ConversationLog::find($id);
    }

    public function create(array $data): ConversationLog
    {
        return ConversationLog::create($data);
    }

    public function update(ConversationLog $log, array $data): bool
    {
        return $log->update($data);
    }

    public function delete(ConversationLog $log): bool
    {
        return $log->delete();
    }

    public function upsert(array $data): ConversationLog
    {
        return ConversationLog::updateOrCreate(
            [
                'user_id' => $data['user_id'],
                'chat_id' => $data['chat_id'],
                'message_id' => $data['message_id']
            ],
            $data
        );
    }

    /**
     * Query Operations
     */
    public function findByConversationId(string $conversationId): Collection
    {
        return Cache::remember("conversation_logs:{$conversationId}", 300, function () use ($conversationId) {
            return ConversationLog::where('conv_id', $conversationId)
                ->orderBy('created_at', 'asc')
                ->get();
        });
    }

    public function findByUserId(int $userId): Collection
    {
        return ConversationLog::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findByMessageId(int $messageId): ?ConversationLog
    {
        return Cache::remember("conversation_log:message:{$messageId}", 300, function () use ($messageId) {
            return ConversationLog::where('message_id', $messageId)->first();
        });
    }

    public function findByChatId(int $chatId): Collection
    {
        return ConversationLog::where('chat_id', $chatId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Conversation Management
     */
    public function findActiveConversations(): Collection
    {
        return Cache::remember('active_conversations', 300, function () {
            return ConversationLog::select('conv_id')
                ->whereNotNull('conv_id')
                ->where('created_at', '>=', now()->subHours(24))
                ->groupBy('conv_id')
                ->having(DB::raw('COUNT(*)'), '>', 1)
                ->get();
        });
    }

    public function findConversationByParticipants(int $userId1, int $userId2): Collection
    {
        $conversationIds = ConversationLog::select('conv_id')
            ->whereIn('user_id', [$userId1, $userId2])
            ->whereNotNull('conv_id')
            ->groupBy('conv_id')
            ->havingRaw('COUNT(DISTINCT user_id) = 2')
            ->pluck('conv_id');

        return ConversationLog::whereIn('conv_id', $conversationIds)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function markAsAction(ConversationLog $log): bool
    {
        return $this->update($log, ['is_action' => 1]);
    }

    /**
     * Statistics Operations
     */
    public function countMessagesByUser(int $userId): int
    {
        return Cache::remember("user_message_count:{$userId}", 3600, function () use ($userId) {
            return ConversationLog::where('user_id', $userId)->count();
        });
    }

    public function countConversationsByUser(int $userId): int
    {
        return Cache::remember("user_conversation_count:{$userId}", 3600, function () use ($userId) {
            return ConversationLog::where('user_id', $userId)
                ->whereNotNull('conv_id')
                ->distinct('conv_id')
                ->count();
        });
    }

    public function findMostActiveUsers(int $limit = 10): Collection
    {
        return Cache::remember("most_active_users:{$limit}", 3600, function () use ($limit) {
            return ConversationLog::select('user_id', DB::raw('COUNT(*) as message_count'))
                ->groupBy('user_id')
                ->orderByDesc('message_count')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Cleanup Operations
     */
    public function deleteOldLogs(int $daysOld): int
    {
        $cutoffDate = now()->subDays($daysOld);

        return ConversationLog::where('created_at', '<=', $cutoffDate)->delete();
    }

    public function findLogsForCleanup(int $daysOld): Collection
    {
        $cutoffDate = now()->subDays($daysOld);

        return ConversationLog::where('created_at', '<=', $cutoffDate)
            ->orderBy('created_at', 'asc')
            ->get();
    }
}
