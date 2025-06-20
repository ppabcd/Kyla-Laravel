<?php

namespace App\Domain\Entities;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * Pair Domain Entity
 * 
 * Represents a matching pair between two users
 */
class Pair extends Model
{
    protected $table = 'pairs';

    protected $fillable = [
        'user_id',
        'partner_id',
        'status',
        'started_at',
        'ended_at',
        'ended_by',
        'end_reason',
        'rating_user',
        'rating_partner',
        'conversation_count',
        'last_message_at',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'partner_id' => 'integer',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'ended_by' => 'integer',
        'rating_user' => 'decimal:2',
        'rating_partner' => 'decimal:2',
        'conversation_count' => 'integer',
        'last_message_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function endedByUser()
    {
        return $this->belongsTo(User::class, 'ended_by');
    }

    /**
     * Business Logic Methods
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isEnded(): bool
    {
        return $this->status === 'ended';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function getDuration(): ?int
    {
        if (!$this->started_at) {
            return null;
        }

        $endTime = $this->ended_at ?? now();
        return $this->started_at->diffInMinutes($endTime);
    }

    public function getOtherUser(int $userId): ?User
    {
        if ($this->user_id === $userId) {
            return $this->partner;
        } elseif ($this->partner_id === $userId) {
            return $this->user;
        }

        return null;
    }

    public function hasUser(int $userId): bool
    {
        return $this->user_id === $userId || $this->partner_id === $userId;
    }

    public function start(): void
    {
        $this->status = 'active';
        $this->started_at = now();
        $this->save();
    }

    public function end(int $endedBy, string $reason = null): void
    {
        $this->status = 'ended';
        $this->ended_at = now();
        $this->ended_by = $endedBy;
        $this->end_reason = $reason;
        $this->save();
    }

    public function addRating(int $raterUserId, float $rating): bool
    {
        if ($raterUserId === $this->user_id) {
            $this->rating_user = $rating;
            $this->partner->addRating($rating);
        } elseif ($raterUserId === $this->partner_id) {
            $this->rating_partner = $rating;
            $this->user->addRating($rating);
        } else {
            return false;
        }

        $this->save();
        return true;
    }

    public function incrementConversationCount(): void
    {
        $this->conversation_count++;
        $this->last_message_at = now();
        $this->save();
    }

    public function getAverageRating(): float
    {
        $ratings = collect([$this->rating_user, $this->rating_partner])
            ->filter()
            ->values();

        return $ratings->isEmpty() ? 0.0 : $ratings->average();
    }
}
