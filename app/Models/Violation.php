<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Violation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'content',
        'violation_type',
        'severity',
        'action_taken',
        'ban_duration_minutes',
        'detected_at',
    ];

    protected function casts(): array
    {
        return [
            'detected_at' => 'datetime',
            'severity' => 'integer',
            'ban_duration_minutes' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isPromotionViolation(): bool
    {
        return $this->violation_type === 'promotion';
    }

    public function isSpamViolation(): bool
    {
        return $this->violation_type === 'spam';
    }

    public function isInappropriateViolation(): bool
    {
        return $this->violation_type === 'inappropriate';
    }

    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('detected_at', '>=', now()->subHours($hours));
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('violation_type', $type);
    }

    public function scopeBySeverity($query, int $severity)
    {
        return $query->where('severity', $severity);
    }
}
