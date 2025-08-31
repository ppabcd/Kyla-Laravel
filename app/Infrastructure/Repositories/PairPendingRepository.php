<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Repositories\PairPendingRepositoryInterface;
use App\Models\PairPending;
use Illuminate\Database\Eloquent\Collection;

/**
 * Pair Pending Repository Implementation
 *
 * Infrastructure layer implementation of PairPendingRepositoryInterface
 */
class PairPendingRepository implements PairPendingRepositoryInterface
{
    /**
     * Normalize gender/interest value to integer codes used in pair_pendings.
     * male => 1, female => 2. Returns null if unknown or "all".
     */
    private function normalizeGenderValue(string|int|null $value): ?int
    {
        if ($value === null) {
            return null;
        }

        if (is_int($value)) {
            return $value;
        }

        $lower = strtolower($value);

        return match ($lower) {
            'male' => 1,
            'female' => 2,
            default => null,
        };
    }

    /**
     * Basic CRUD Operations
     */
    public function findById(int $id): ?object
    {
        return PairPending::find($id);
    }

    public function create(array $data): object
    {
        if (array_key_exists('gender', $data)) {
            $data['gender'] = $this->normalizeGenderValue($data['gender']);
        }
        if (array_key_exists('interest', $data)) {
            $data['interest'] = $this->normalizeGenderValue($data['interest']);
        }

        return PairPending::create($data);
    }

    public function update(object $pairPending, array $data): bool
    {
        if (array_key_exists('gender', $data)) {
            $data['gender'] = $this->normalizeGenderValue($data['gender']);
        }
        if (array_key_exists('interest', $data)) {
            $data['interest'] = $this->normalizeGenderValue($data['interest']);
        }

        return $pairPending->update($data);
    }

    public function delete(object $pairPending): bool
    {
        return $pairPending->delete();
    }

    /**
     * User Operations
     */
    public function findByUserId(int $userId): ?object
    {
        return PairPending::where('user_id', $userId)->first();
    }

    public function findPendingPairs(): Collection
    {
        return PairPending::orderBy('created_at', 'ASC')
            ->orderBy('id', 'ASC')
            ->get();
    }

    public function clearUserPendingPair(int $userId): bool
    {
        return PairPending::where('user_id', $userId)->delete() > 0;
    }

    /**
     * Matching Operations
     */
    public function findNextPendingPair(int $userId): ?object
    {
        return PairPending::where('user_id', '!=', $userId)
            ->orderBy('created_at', 'ASC')
            ->orderBy('id', 'ASC')
            ->first();
    }

    public function countPendingPairs(): int
    {
        return PairPending::count();
    }

    public function findAvailableMatch(string $userGender, string $targetGender): ?object
    {
        $genderValue = $this->normalizeGenderValue($targetGender);
        $interestValue = $this->normalizeGenderValue($userGender);

        $query = PairPending::query();

        if ($genderValue !== null) {
            $query->where('gender', $genderValue);
        }

        // Match users who want this user's gender, or who accept all (null)
        if ($interestValue !== null) {
            $query->where(function ($q) use ($interestValue) {
                $q->where('interest', $interestValue)
                    ->orWhereNull('interest');
            });
        }

        return $query->orderBy('created_at', 'ASC')
            ->orderBy('id', 'ASC')
            ->first();
    }

    public function deleteByUserId(int $userId): bool
    {
        return PairPending::where('user_id', $userId)->delete() > 0;
    }

    /**
     * Queue Management Operations
     */
    public function isQueueOvercrowded(int $threshold = 5): bool
    {
        return $this->countPendingPairs() > $threshold;
    }

    public function getGenderBalance(): array
    {
        $counts = PairPending::selectRaw('gender, COUNT(*) as count')
            ->groupBy('gender')
            ->pluck('count', 'gender')
            ->toArray();

        return [
            'male_count' => $counts[1] ?? 0,
            'female_count' => $counts[2] ?? 0,
            'total_count' => array_sum($counts),
            'is_balanced' => $this->isGenderBalanced($counts),
        ];
    }

    public function isGenderBalanced(?array $counts = null): bool
    {
        if ($counts === null) {
            $counts = PairPending::selectRaw('gender, COUNT(*) as count')
                ->groupBy('gender')
                ->pluck('count', 'gender')
                ->toArray();
        }

        $maleCount = $counts[1] ?? 0;
        $femaleCount = $counts[2] ?? 0;
        $total = $maleCount + $femaleCount;

        if ($total === 0) {
            return true;
        }

        $ratio = min($maleCount, $femaleCount) / max($maleCount, $femaleCount);

        return $ratio >= 0.6; // 60% balance threshold
    }

    public function getUnderrepresentedGender(): ?int
    {
        $balance = $this->getGenderBalance();

        if ($balance['is_balanced']) {
            return null;
        }

        return $balance['male_count'] < $balance['female_count'] ? 1 : 2;
    }
}
