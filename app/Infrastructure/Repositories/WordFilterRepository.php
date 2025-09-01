<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\WordFilter;
use App\Domain\Repositories\WordFilterRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class WordFilterRepository implements WordFilterRepositoryInterface
{
    /**
     * Get all word filters with pagination
     */
    public function getFilteredWords(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = WordFilter::query();

        // Apply filters
        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (! empty($filters['type'])) {
            $query->byType($filters['type']);
        }

        if (isset($filters['ai_check'])) {
            if ($filters['ai_check'] === '1') {
                $query->where('is_open_ai_check', true);
            } elseif ($filters['ai_check'] === '0') {
                $query->where('is_open_ai_check', false);
            }
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Find word filter by ID
     */
    public function findById(int $id): ?WordFilter
    {
        return WordFilter::find($id);
    }

    /**
     * Create new word filter
     */
    public function create(array $data): WordFilter
    {
        $wordFilter = WordFilter::create($data);
        $this->clearCache();

        return $wordFilter;
    }

    /**
     * Update word filter
     */
    public function update(int $id, array $data): bool
    {
        $result = WordFilter::where('id', $id)->update($data);
        if ($result) {
            $this->clearCache();
        }

        return $result;
    }

    /**
     * Delete word filter
     */
    public function delete(int $id): bool
    {
        $result = WordFilter::where('id', $id)->delete();
        if ($result) {
            $this->clearCache();
        }

        return $result;
    }

    /**
     * Check if word exists in filter
     */
    public function wordExists(string $word, int $type): bool
    {
        return WordFilter::where('word', $word)
            ->where('word_type', $type)
            ->exists();
    }

    /**
     * Get words by type
     */
    public function getWordsByType(int $type): Collection
    {
        return Cache::remember("word_filter_type_{$type}", 3600, function () use ($type) {
            return WordFilter::byType($type)->get();
        });
    }

    /**
     * Get word filter statistics
     */
    public function getStatistics(): array
    {
        return Cache::remember('word_filter_stats', 600, function () {
            $stats = [
                'total_words' => WordFilter::count(),
                'ai_checked' => WordFilter::where('is_open_ai_check', true)->count(),
                'by_type' => [],
            ];

            foreach (WordFilter::getWordTypes() as $typeId => $typeName) {
                $stats['by_type'][$typeName] = WordFilter::byType($typeId)->count();
            }

            return $stats;
        });
    }

    /**
     * Bulk import words
     */
    public function bulkImport(array $words, int $type): int
    {
        $imported = 0;
        $existingWords = WordFilter::byType($type)->pluck('word')->toArray();

        foreach ($words as $word) {
            $word = trim(strtolower($word));
            if (! empty($word) && ! in_array($word, $existingWords)) {
                WordFilter::create([
                    'word' => $word,
                    'word_type' => $type,
                    'is_open_ai_check' => false,
                ]);
                $existingWords[] = $word;
                $imported++;
            }
        }

        if ($imported > 0) {
            $this->clearCache();
        }

        return $imported;
    }

    /**
     * Search words in filter
     */
    public function searchWords(string $query): Collection
    {
        return WordFilter::search($query)->limit(50)->get();
    }

    /**
     * Get recent words
     */
    public function getRecentWords(int $limit = 10): Collection
    {
        return Cache::remember("word_filter_recent_{$limit}", 300, function () use ($limit) {
            return WordFilter::orderBy('created_at', 'desc')->limit($limit)->get();
        });
    }

    /**
     * Clear word filter cache
     */
    private function clearCache(): void
    {
        Cache::forget('word_filter_stats');

        // Clear type-specific caches
        foreach (array_keys(WordFilter::getWordTypes()) as $type) {
            Cache::forget("word_filter_type_{$type}");
        }

        // Clear recent words cache
        for ($i = 5; $i <= 50; $i += 5) {
            Cache::forget("word_filter_recent_{$i}");
        }
    }
}
