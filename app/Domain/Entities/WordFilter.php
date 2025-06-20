<?php

namespace App\Domain\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WordFilter extends Model
{
    use HasFactory;

    protected $table = 'word_filter';

    protected $fillable = [
        'word',
        'word_type',
        'is_open_ai_check',
    ];

    protected $casts = [
        'is_open_ai_check' => 'boolean',
        'word_type' => 'integer',
    ];

    // Word filter types constants
    public const TYPE_PROFANITY = 1;
    public const TYPE_SPAM = 2;
    public const TYPE_INAPPROPRIATE = 3;
    public const TYPE_HATE_SPEECH = 4;
    public const TYPE_VIOLENCE = 5;
    public const TYPE_ADULT_CONTENT = 6;

    /**
     * Get word type name
     */
    public function getWordTypeNameAttribute(): string
    {
        return match ($this->word_type) {
            self::TYPE_PROFANITY => 'Profanity',
            self::TYPE_SPAM => 'Spam',
            self::TYPE_INAPPROPRIATE => 'Inappropriate',
            self::TYPE_HATE_SPEECH => 'Hate Speech',
            self::TYPE_VIOLENCE => 'Violence',
            self::TYPE_ADULT_CONTENT => 'Adult Content',
            default => 'Unknown',
        };
    }

    /**
     * Get word type color for badges
     */
    public function getWordTypeColorAttribute(): string
    {
        return match ($this->word_type) {
            self::TYPE_PROFANITY => 'red',
            self::TYPE_SPAM => 'yellow',
            self::TYPE_INAPPROPRIATE => 'orange',
            self::TYPE_HATE_SPEECH => 'purple',
            self::TYPE_VIOLENCE => 'red',
            self::TYPE_ADULT_CONTENT => 'pink',
            default => 'gray',
        };
    }

    /**
     * Get all word types
     */
    public static function getWordTypes(): array
    {
        return [
            self::TYPE_PROFANITY => 'Profanity',
            self::TYPE_SPAM => 'Spam',
            self::TYPE_INAPPROPRIATE => 'Inappropriate',
            self::TYPE_HATE_SPEECH => 'Hate Speech',
            self::TYPE_VIOLENCE => 'Violence',
            self::TYPE_ADULT_CONTENT => 'Adult Content',
        ];
    }

    /**
     * Scope for filtering by word type
     */
    public function scopeByType($query, int $type)
    {
        return $query->where('word_type', $type);
    }

    /**
     * Scope for AI-checked words
     */
    public function scopeAiChecked($query)
    {
        return $query->where('is_open_ai_check', true);
    }

    /**
     * Scope for searching words
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where('word', 'LIKE', "%{$search}%");
    }
}
