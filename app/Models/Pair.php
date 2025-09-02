<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pair extends Model
{
    use HasFactory;

    protected $table = 'pairs';

    protected $fillable = [
        'user_id',
        'partner_id',
        'status',
        'active',
        'started_at',
        'ended_at',
        'ended_by_user_id',
        'ended_reason',
        'metadata',
    ];

    protected $casts = [
        'active' => 'boolean',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'metadata' => 'json',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function endedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ended_by_user_id');
    }

    /**
     * Get the partner user for the given user
     */
    public function getPartner(User $user): ?User
    {
        if ($this->user_id === $user->id) {
            return $this->partner;
        } elseif ($this->partner_id === $user->id) {
            return $this->user;
        }

        return null;
    }

    /**
     * Get the other user in the pair
     */
    public function getOtherUser(int $userId): ?User
    {
        if ($this->user_id === $userId) {
            return $this->partner;
        } elseif ($this->partner_id === $userId) {
            return $this->user;
        }

        return null;
    }

    /**
     * Check if the pair is active
     */
    public function isActive(): bool
    {
        return $this->active && $this->status === 'active';
    }
}
