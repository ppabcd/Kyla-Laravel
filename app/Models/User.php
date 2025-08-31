<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'telegram_id',
        'first_name',
        'last_name',
        'username',
        'language_code',
        'is_bot',
        'gender',
        'gender_icon',
        'interest',
        'age',
        'premium',
        'banned',
        'ban_type',
        'ban_x_times',
        'is_auto',
        'platform_id',
        'is_blocked',
        'is_safe_mode',
        'is_get_announcement',
        'soft_ban',
        'soft_banned_until',
        'soft_ban_reason',
        'promotion_violation_count',
        'last_promotion_violation_at',
        'balances',
        'next_update_balance',
        'is_new_user',
        'checked_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'soft_banned_until' => 'datetime',
            'last_promotion_violation_at' => 'datetime',
            'is_banned' => 'boolean',
            'is_premium' => 'boolean',
            'is_blocked' => 'boolean',
            'is_safe_mode' => 'boolean',
            'promotion_violation_count' => 'integer',
        ];
    }

    /**
     * Get user's pairs as first user
     */
    public function pairsAsFirst(): HasMany
    {
        return $this->hasMany(Pair::class, 'first_user_id');
    }

    /**
     * Get user's pairs as second user
     */
    public function pairsAsSecond(): HasMany
    {
        return $this->hasMany(Pair::class, 'second_user_id');
    }

    /**
     * Get all user's pairs
     */
    public function pairs()
    {
        return $this->pairsAsFirst()->union($this->pairsAsSecond());
    }

    /**
     * Get user's pending pairs
     */
    public function pendingPairs(): HasMany
    {
        return $this->hasMany(PairPending::class);
    }

    /**
     * Get user's messages
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get user's media
     */
    public function media(): HasMany
    {
        return $this->hasMany(Media::class);
    }

    /**
     * Get user's session
     */
    public function session(): HasOne
    {
        return $this->hasOne(Session::class);
    }

    /**
     * Get user's location
     */
    public function location(): HasOne
    {
        return $this->hasOne(UserLocation::class);
    }

    /**
     * Get user's rating
     */
    public function rating(): HasOne
    {
        return $this->hasOne(Rating::class);
    }

    /**
     * Get user's conversation logs
     */
    public function conversationLogs(): HasMany
    {
        return $this->hasMany(ConversationLog::class);
    }

    /**
     * Get user's violations
     */
    public function violations(): HasMany
    {
        return $this->hasMany(Violation::class);
    }

    /**
     * Check if user is banned
     */
    public function isBanned(): bool
    {
        return $this->banned === 1;
    }

    /**
     * Check if user is premium
     */
    public function isPremium(): bool
    {
        return $this->premium === 1;
    }

    /**
     * Check if user is blocked
     */
    public function isBlocked(): bool
    {
        return $this->is_blocked === 1;
    }

    /**
     * Check if user is in safe mode
     */
    public function isInSafeMode(): bool
    {
        return $this->is_safe_mode === 1;
    }

    /**
     * Check if user is currently soft banned
     */
    public function isSoftBanned(): bool
    {
        return $this->soft_banned_until && $this->soft_banned_until->isFuture();
    }

    /**
     * Apply soft ban to user
     */
    public function applySoftBan(int $durationMinutes, string $reason): bool
    {
        $this->soft_banned_until = now()->addMinutes($durationMinutes);
        $this->soft_ban_reason = $reason;

        return $this->save();
    }

    /**
     * Remove soft ban from user
     */
    public function removeSoftBan(): bool
    {
        $this->soft_banned_until = null;
        $this->soft_ban_reason = null;

        return $this->save();
    }

    /**
     * Get recent violations by type
     */
    public function getRecentViolations(string $type, int $hours = 24): int
    {
        return $this->violations()
            ->where('violation_type', $type)
            ->where('detected_at', '>=', now()->subHours($hours))
            ->count();
    }

    /**
     * Check if user should be soft banned for promotion violations
     */
    public function shouldBeSoftBannedForPromotion(): bool
    {
        $recentPromotions = $this->getRecentViolations('promotion', 1); // Last hour

        return $recentPromotions >= 3; // 3 strikes in 1 hour
    }

    /**
     * Check if user is new
     */
    public function isNew(): bool
    {
        return $this->is_new_user;
    }

    /**
     * Get user's full name
     */
    public function getFullNameAttribute(): string
    {
        $parts = [];

        if ($this->first_name) {
            $parts[] = $this->first_name;
        }

        if ($this->last_name) {
            $parts[] = $this->last_name;
        }

        return implode(' ', $parts) ?: 'Unknown User';
    }

    /**
     * Get user's display name
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->username) {
            return "@{$this->username}";
        }

        return $this->full_name;
    }

    /**
     * Get user's current balance
     */
    public function getCurrentBalanceAttribute(): int
    {
        return $this->balances ?? 0;
    }

    /**
     * Check if user has sufficient balance
     */
    public function hasBalance(int $amount): bool
    {
        return $this->current_balance >= $amount;
    }

    /**
     * Deduct balance from user
     */
    public function deductBalance(int $amount): bool
    {
        if (! $this->hasBalance($amount)) {
            return false;
        }

        $this->balances = $this->current_balance - $amount;

        return $this->save();
    }

    /**
     * Add balance to user
     */
    public function addBalance(int $amount): bool
    {
        $this->balances = $this->current_balance + $amount;

        return $this->save();
    }

    /**
     * Get user's active pair
     */
    public function getActivePair(): ?Pair
    {
        return $this->pairsAsFirst()
            ->where('active', true)
            ->orWhere(function ($query) {
                $query->where('second_user_id', $this->id)
                    ->where('active', true);
            })
            ->first();
    }

    /**
     * Get user's partner in active pair
     */
    public function getActivePartner(): ?User
    {
        $pair = $this->getActivePair();

        if (! $pair) {
            return null;
        }

        $partnerId = $pair->first_user_id === $this->id
            ? $pair->second_user_id
            : $pair->first_user_id;

        return User::find($partnerId);
    }

    /**
     * Check if user has active pair
     */
    public function hasActivePair(): bool
    {
        return $this->getActivePair() !== null;
    }

    /**
     * Check if user has pending pair
     */
    public function hasPendingPair(): bool
    {
        return $this->pendingPairs()->where('active', true)->exists();
    }

    /**
     * Get user's average rating
     */
    public function getAverageRatingAttribute(): float
    {
        return $this->rating?->avg_rating ?? 0.0;
    }

    /**
     * Get user's total ratings
     */
    public function getTotalRatingsAttribute(): int
    {
        return $this->rating?->total_rating ?? 0;
    }

    /**
     * Scope to get banned users
     */
    public function scopeBanned($query)
    {
        return $query->where('banned', 1);
    }

    /**
     * Scope to get premium users
     */
    public function scopePremium($query)
    {
        return $query->where('premium', 1);
    }

    /**
     * Scope to get active users
     */
    public function scopeActive($query)
    {
        return $query->where('banned', 0)->where('is_blocked', 0);
    }

    /**
     * Scope to get users by gender
     */
    public function scopeByGender($query, int $gender)
    {
        return $query->where('gender', $gender);
    }

    /**
     * Scope to get users by interest
     */
    public function scopeByInterest($query, int $interest)
    {
        return $query->where('interest', $interest);
    }

    /**
     * Scope to get users by language
     */
    public function scopeByLanguage($query, string $language)
    {
        return $query->where('language_code', $language);
    }

    /**
     * Scope to get new users
     */
    public function scopeNew($query)
    {
        return $query->where('is_new_user', true);
    }

    /**
     * Scope to get users created in date range
     */
    public function scopeCreatedBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope to get users active in date range
     */
    public function scopeActiveBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('updated_at', [$startDate, $endDate]);
    }
}
