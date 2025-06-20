<?php

namespace App\Domain\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

/**
 * User Domain Entity
 * 
 * This entity represents a user in the domain layer,
 * following Clean Architecture principles.
 */
class User extends Model
{
    protected $table = 'users';

    protected $fillable = [
        'telegram_id',
        'first_name',
        'last_name',
        'username',
        'language_code',
        'age',
        'gender',
        'interest',
        'region',
        'profile_picture',
        'about_me',
        'balance',
        'is_premium',
        'premium_expires_at',
        'is_banned',
        'banned_reason',
        'banned_at',
        'soft_ban',
        'last_activity_at',
        'bot_mode',
        'verification_status',
        'settings',
        'location_latitude',
        'location_longitude',
        'visibility',
        'auto_search',
        'safe_mode',
        'notification_enabled',
        'search_radius',
        'captcha_verified_at',
        'referral_code',
        'referred_by',
        'total_referrals',
        'total_matches',
        'total_messages_sent',
        'total_time_chatting',
        'rating_sum',
        'rating_count',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'telegram_id' => 'integer',
        'age' => 'integer',
        'balance' => 'decimal:2',
        'is_premium' => 'boolean',
        'premium_expires_at' => 'datetime',
        'is_banned' => 'boolean',
        'banned_at' => 'datetime',
        'soft_ban' => 'datetime',
        'last_activity_at' => 'datetime',
        'settings' => 'array',
        'location_latitude' => 'decimal:8',
        'location_longitude' => 'decimal:8',
        'visibility' => 'boolean',
        'auto_search' => 'boolean',
        'safe_mode' => 'boolean',
        'notification_enabled' => 'boolean',
        'search_radius' => 'integer',
        'captcha_verified_at' => 'datetime',
        'total_referrals' => 'integer',
        'total_matches' => 'integer',
        'total_messages_sent' => 'integer',
        'total_time_chatting' => 'integer',
        'rating_sum' => 'decimal:2',
        'rating_count' => 'integer'
    ];

    protected $hidden = [
        'location_latitude',
        'location_longitude',
        'banned_reason',
        'settings'
    ];

    /**
     * Business Logic Methods
     */

    public function isPremium(): bool
    {
        return $this->is_premium &&
            ($this->premium_expires_at === null || $this->premium_expires_at->isFuture());
    }

    public function isBanned(): bool
    {
        return $this->is_banned;
    }

    public function isSoftBanned(): bool
    {
        return $this->soft_ban !== null && $this->soft_ban->isFuture();
    }

    public function isActive(): bool
    {
        return $this->last_activity_at &&
            $this->last_activity_at->diffInMinutes(now()) <= 30;
    }

    public function canMatch(): bool
    {
        return !$this->isBanned() &&
            !$this->isSoftBanned() &&
            $this->verification_status === 'verified' &&
            $this->visibility;
    }

    public function getAverageRating(): float
    {
        if ($this->rating_count === 0) {
            return 0.0;
        }

        return round($this->rating_sum / $this->rating_count, 2);
    }

    public function addRating(float $rating): void
    {
        $this->rating_sum += $rating;
        $this->rating_count += 1;
        $this->save();
    }

    public function incrementBalance(float $amount, ?string $reason = null): void
    {
        $this->balance += $amount;
        $this->save();

        // Log transaction
        $this->balanceTransactions()->create([
            'type' => 'credit',
            'amount' => $amount,
            'previous_balance' => $this->balance - $amount,
            'current_balance' => $this->balance,
            'description' => $reason ?? 'Balance increment'
        ]);
    }

    public function decrementBalance(float $amount, ?string $reason = null): bool
    {
        if ($this->balance < $amount) {
            return false;
        }

        $previousBalance = $this->balance;
        $this->balance -= $amount;
        $this->save();

        // Log transaction
        $this->balanceTransactions()->create([
            'type' => 'debit',
            'amount' => $amount,
            'previous_balance' => $previousBalance,
            'current_balance' => $this->balance,
            'description' => $reason ?? 'Balance decrement'
        ]);

        return true;
    }

    public function ban(string $reason): void
    {
        $this->is_banned = true;
        $this->banned_reason = $reason;
        $this->banned_at = now();
        $this->save();
    }

    public function unban(): void
    {
        $this->is_banned = false;
        $this->banned_reason = null;
        $this->banned_at = null;
        $this->save();
    }

    public function softBan(int $minutes): void
    {
        $this->soft_ban = now()->addMinutes($minutes);
        $this->save();
    }

    public function upgradeToPremium(int $days = 30): void
    {
        $this->is_premium = true;
        $this->premium_expires_at = $this->isPremium()
            ? $this->premium_expires_at->addDays($days)
            : now()->addDays($days);
        $this->save();
    }

    public function updateActivity(): void
    {
        $this->last_activity_at = now();
        $this->save();
    }

    public function getFullName(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function getDisplayName(): string
    {
        return $this->username
            ? '@' . $this->username
            : $this->getFullName();
    }

    public function hasLocation(): bool
    {
        return $this->location_latitude !== null && $this->location_longitude !== null;
    }

    public function distanceTo(User $otherUser): ?float
    {
        if (!$this->hasLocation() || !$otherUser->hasLocation()) {
            return null;
        }

        // Haversine formula to calculate distance
        $earthRadius = 6371; // Earth's radius in kilometers

        $lat1 = deg2rad($this->location_latitude);
        $lon1 = deg2rad($this->location_longitude);
        $lat2 = deg2rad($otherUser->location_latitude);
        $lon2 = deg2rad($otherUser->location_longitude);

        $deltaLat = $lat2 - $lat1;
        $deltaLon = $lon2 - $lon1;

        $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
            cos($lat1) * cos($lat2) *
            sin($deltaLon / 2) * sin($deltaLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Relationships
     */

    public function conversationLogs(): HasMany
    {
        return $this->hasMany(ConversationLog::class, 'user_id');
    }

    public function balanceTransactions(): HasMany
    {
        return $this->hasMany(BalanceTransaction::class, 'user_id');
    }

    public function pairs(): HasMany
    {
        return $this->hasMany(Pair::class, 'user_id');
    }

    public function partnerPairs(): HasMany
    {
        return $this->hasMany(Pair::class, 'partner_id');
    }

    public function allPairs()
    {
        return $this->pairs()->union($this->partnerPairs());
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'reported_user_id');
    }

    public function submittedReports(): HasMany
    {
        return $this->hasMany(Report::class, 'reporter_user_id');
    }

    public function referredUsers(): HasMany
    {
        return $this->hasMany(User::class, 'referred_by');
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referred_by');
    }
}
