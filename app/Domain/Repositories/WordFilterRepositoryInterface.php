<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\WordFilter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface WordFilterRepositoryInterface
{
    /**
     * Get all word filters with pagination
     */
    public function getFilteredWords(array $filters = [], int $perPage = 20): LengthAwarePaginator;

    /**
     * Find word filter by ID
     */
    public function findById(int $id): ?WordFilter;

    /**
     * Create new word filter
     */
    public function create(array $data): WordFilter;

    /**
     * Update word filter
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete word filter
     */
    public function delete(int $id): bool;

    /**
     * Check if word exists in filter
     */
    public function wordExists(string $word, int $type): bool;

    /**
     * Get words by type
     */
    public function getWordsByType(int $type): Collection;

    /**
     * Get word filter statistics
     */
    public function getStatistics(): array;

    /**
     * Bulk import words
     */
    public function bulkImport(array $words, int $type): int;

    /**
     * Search words in filter
     */
    public function searchWords(string $query): Collection;

    /**
     * Get recent words
     */
    public function getRecentWords(int $limit = 10): Collection;
}
