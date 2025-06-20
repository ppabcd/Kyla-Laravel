<?php

namespace App\Services;

use App\Domain\Repositories\PairPendingRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class PendingService
{
    public function __construct(
        private PairPendingRepositoryInterface $pairPendingRepository
    ) {
    }

    /**
     * Get pending count for user
     */
    public function getPendingCount(User $user): int
    {
        try {
            $pendingPairs = $this->pairPendingRepository->findByUserId($user->id);
            return count($pendingPairs);
        } catch (\Exception $e) {
            Log::error('Failed to get pending count', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Check if user has pending pairs
     */
    public function hasPendingPairs(User $user): bool
    {
        return $this->getPendingCount($user) > 0;
    }

    /**
     * Get total pending pairs in system
     */
    public function getTotalPendingCount(): int
    {
        // This would require a different method in the repository
        // For now, return 0
        return 0;
    }

    /**
     * Clean expired pending pairs
     */
    public function cleanExpiredPendingPairs(): int
    {
        // This would clean up old pending pairs
        // Implementation depends on your business logic
        return 0;
    }
}
