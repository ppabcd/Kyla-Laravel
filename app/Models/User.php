<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
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
        'gender',
        'gender_icon',
        'interest',
        'age',
        'location',
        'is_premium',
        'is_banned',
        'is_searching',
        'banned_reason',
        'premium_expires_at',
        'last_activity_at',
        'banned_at',
        'settings',
        'metadata',
        'balance',
        'safe_mode',
        'last_message_at',
        'soft_banned_until',
        'soft_ban_reason',
        'promotion_violation_count',
        'last_promotion_violation_at',
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
            'safe_mode' => 'boolean',
            'promotion_violation_count' => 'integer',
        ];
    }

    /**
     * Get user's pending pairs
     */
    public function pendingPairs(): HasMany
    {
        return $this->hasMany(PairPending::class);
    }

    /**
     * Get user's location record
     */
    public function userLocation(): HasOne
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
        return $this->hasMany(ConversationLog::class, 'user_id');
    }

    /**
     * Get user's balance transactions
     */
    public function balanceTransactions(): HasMany
    {
        return $this->hasMany(BalanceTransaction::class, 'user_id');
    }

    /**
     * Get user's pairs as first user
     */
    public function pairs(): HasMany
    {
        return $this->hasMany(Pair::class, 'user_id');
    }

    /**
     * Get user's pairs as partner
     */
    public function partnerPairs(): HasMany
    {
        return $this->hasMany(Pair::class, 'partner_id');
    }

    /**
     * Get all user's pairs (as user or partner)
     */
    public function allPairs()
    {
        return $this->pairs()->union($this->partnerPairs());
    }

    /**
     * Get user's reports (reports about this user)
     */
    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'reported_user_id');
    }

    /**
     * Get user's submitted reports
     */
    public function submittedReports(): HasMany
    {
        return $this->hasMany(Report::class, 'reporter_user_id');
    }

    /**
     * Get users referred by this user
     */
    public function referredUsers(): HasMany
    {
        return $this->hasMany(User::class, 'referred_by');
    }

    /**
     * Get the user who referred this user
     */
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_by');
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
        return $this->is_banned;
    }

    /**
     * Check if user is currently soft banned
     */
    public function isSoftBanned(): bool
    {
        return $this->soft_banned_until !== null && $this->soft_banned_until->isFuture();
    }

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->last_activity_at &&
            $this->last_activity_at->diffInMinutes(now()) <= 30;
    }

    /**
     * Check if user can be matched
     */
    public function canMatch(): bool
    {
        return ! $this->isBanned() &&
            ! $this->isSoftBanned() &&
            $this->gender &&
            $this->interest &&
            ! $this->is_searching; // Not currently searching for another match
    }

    /**
     * Get average rating
     */
    public function getAverageRating(): float
    {
        if ($this->rating_count === 0) {
            return 0.0;
        }

        return round($this->rating_sum / $this->rating_count, 2);
    }

    /**
     * Get average rating attribute
     */
    public function getAverageRatingAttribute(): float
    {
        $ratings = $this->hasMany(Rating::class, 'rated_user_id')->get();

        if ($ratings->isEmpty()) {
            return 0.0;
        }

        return round($ratings->avg('rating'), 1);
    }

    /**
     * Get total ratings attribute
     */
    public function getTotalRatingsAttribute(): int
    {
        return $this->hasMany(Rating::class, 'rated_user_id')->count();
    }

    /**
     * Check if user is in conversation
     */
    public function isInConversation(): bool
    {
        // Consider pairs where this user is either `user_id` or `partner_id`
        return Pair::where('status', 'active')
            ->where(function ($query) {
                $query->where('user_id', $this->id)
                    ->orWhere('partner_id', $this->id);
            })
            ->exists();
    }

    /**
     * Add rating to user
     */
    public function addRating(float $rating): void
    {
        $this->rating_sum += $rating;
        $this->rating_count += 1;
        $this->save();
    }

    /**
     * Increment user balance
     */
    public function incrementBalance(float $amount, ?string $reason = null): void
    {
        $this->balance += $amount;
        $this->save();

        // Log transaction
        $this->balanceTransactions()->create([
            'type' => 'credit',
            'amount' => $amount,
            'current_balance' => $this->balance,
            'description' => $reason ?? 'Balance increment',
        ]);
    }

    /**
     * Decrement user balance
     */
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
            'current_balance' => $this->balance,
            'description' => $reason ?? 'Balance decrement',
        ]);

        return true;
    }

    /**
     * Ban user
     */
    public function ban(string $reason): void
    {
        $this->is_banned = true;
        $this->banned_reason = $reason;
        $this->banned_at = now();
        $this->save();
    }

    /**
     * Unban user
     */
    public function unban(): void
    {
        $this->is_banned = false;
        $this->banned_reason = null;
        $this->banned_at = null;
        $this->save();
    }

    /**
     * Soft ban user
     */
    public function softBan(int $minutes): void
    {
        $this->soft_banned_until = now()->addMinutes($minutes);
        $this->save();
    }

    /**
     * Upgrade user to premium
     */
    public function upgradeToPremium(int $days = 30): void
    {
        $this->is_premium = true;
        $this->premium_expires_at = $this->isPremium()
            ? $this->premium_expires_at->addDays($days)
            : now()->addDays($days);
        $this->save();
    }

    /**
     * Update user activity
     */
    public function updateActivity(): void
    {
        $this->last_activity_at = now();
        $this->save();
    }

    /**
     * Get full name
     */
    public function getFullName(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    /**
     * Get full name attribute
     */
    public function getFullNameAttribute(): string
    {
        return $this->getFullName();
    }

    /**
     * Get display name
     */
    public function getDisplayName(): string
    {
        return $this->username
            ? '@'.$this->username
            : $this->getFullName();
    }

    /**
     * Get display name attribute
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->getDisplayName();
    }

    /**
     * Check if user has location
     */
    public function hasLocation(): bool
    {
        return $this->location_latitude !== null && $this->location_longitude !== null;
    }

    /**
     * Calculate distance to another user
     */
    public function distanceTo(User $otherUser): ?float
    {
        if (! $this->hasLocation() || ! $otherUser->hasLocation()) {
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
     * Check if user is premium
     */
    public function isPremium(): bool
    {
        return $this->is_premium;
    }


    /**
     * Check if user is in safe mode
     */
    public function isInSafeMode(): bool
    {
        return (bool) $this->safe_mode;
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
     * Check if user should be soft banned for promotion violations
     */
    public function shouldBeSoftBannedForPromotion(): bool
    {
        $recentPromotions = $this->getRecentViolations('promotion', 1); // Last hour

        return $recentPromotions >= 3; // 3 strikes in 1 hour
    }

    /**
     * Scope to get banned users
     */
    public function scopeBanned($query)
    {
        return $query->where('is_banned', 1);
    }

    /**
     * Scope to get premium users
     */
    public function scopePremium($query)
    {
        return $query->where('is_premium', 1);
    }

    /**
     * Scope to get active users
     */
    public function scopeActive($query)
    {
        return $query->where('is_banned', 0);
    }

    /**
     * Scope to get users by gender
     */
    public function scopeByGender($query, string $gender)
    {
        return $query->where('gender', $gender);
    }

    /**
     * Scope to get users by interest
     */
    public function scopeByInterest($query, string $interest)
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
