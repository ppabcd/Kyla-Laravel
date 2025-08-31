<?php

namespace App\Application\Services;

use App\Models\User;
use App\Models\Violation;
use Illuminate\Support\Facades\Log;

class ViolationService
{
    /**
     * Detect if message contains promotion content
     */
    public function detectPromotion(string $content): bool
    {
        // Remove spaces and normalize content
        $normalizedContent = strtolower(preg_replace('/\s+/', '', $content));

        // Common promotion patterns
        $promotionPatterns = [
            '/@[a-zA-Z0-9_]+/',           // @username mentions
            '/t\.me\/[a-zA-Z0-9_]+/',      // Telegram links
            '/telegram\.me\/[a-zA-Z0-9_]+/', // Telegram.me links
            '/follow\s*me/i',              // "follow me" phrases
            '/add\s*me/i',                 // "add me" phrases
            '/contact\s*me/i',             // "contact me" phrases
            '/my\s*username/i',            // "my username" phrases
            '/find\s*me/i',                // "find me" phrases
            '/(.)\1{7,}/',                 // Repeated characters (8+ times)
        ];

        foreach ($promotionPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Record a violation for a user
     */
    public function recordViolation(User $user, string $content, string $type = 'promotion', int $severity = 1): Violation
    {
        $violation = Violation::create([
            'user_id' => $user->id,
            'content' => $content,
            'violation_type' => $type,
            'severity' => $severity,
            'detected_at' => now(),
        ]);

        // Update user's violation counter
        if ($type === 'promotion') {
            $user->increment('promotion_violation_count');
            $user->update(['last_promotion_violation_at' => now()]);
        }

        Log::info('Violation recorded', [
            'user_id' => $user->id,
            'type' => $type,
            'severity' => $severity,
            'content_preview' => substr($content, 0, 100),
        ]);

        return $violation;
    }

    /**
     * Check if user should be soft banned and apply if necessary
     */
    public function checkAndApplySoftBan(User $user, string $content): bool
    {
        if ($user->isSoftBanned()) {
            return false; // Already soft banned
        }

        $recentPromotions = $user->getRecentViolations('promotion', 1);

        if ($recentPromotions >= 2) { // 3rd strike (2 previous + current)
            $duration = $this->calculateSoftBanDuration($recentPromotions);
            $reason = 'Repeated username/profile promotion';

            $user->applySoftBan($duration, $reason);

            // Record the violation with action taken
            $violation = $this->recordViolation($user, $content, 'promotion', 2);
            $violation->update([
                'action_taken' => 'soft_ban',
                'ban_duration_minutes' => $duration,
            ]);

            Log::warning('User soft banned for promotion violations', [
                'user_id' => $user->id,
                'duration_minutes' => $duration,
                'violation_count' => $recentPromotions + 1,
            ]);

            return true;
        }

        // Just record the violation without ban
        $this->recordViolation($user, $content, 'promotion', 1);

        return false;
    }

    /**
     * Calculate soft ban duration based on violation count
     */
    private function calculateSoftBanDuration(int $violationCount): int
    {
        return match (true) {
            $violationCount >= 5 => 60,  // 1 hour for 6+ violations
            $violationCount >= 3 => 30,  // 30 minutes for 4-5 violations
            $violationCount >= 2 => 15,  // 15 minutes for 3 violations
            default => 10,               // 10 minutes for first soft ban
        };
    }

    /**
     * Check if user's message should be blocked due to soft ban
     */
    public function shouldBlockMessage(User $user): bool
    {
        if (! $user->isSoftBanned()) {
            return false;
        }

        // Clean up expired soft bans
        if ($user->soft_banned_until->isPast()) {
            $user->removeSoftBan();

            return false;
        }

        return true;
    }

    /**
     * Get soft ban remaining time in minutes
     */
    public function getSoftBanRemainingMinutes(User $user): int
    {
        if (! $user->isSoftBanned()) {
            return 0;
        }

        return (int) ceil(now()->diffInMinutes($user->soft_banned_until, false));
    }
}
