<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PairPending extends Model
{
    use HasFactory;

    protected $table = 'pair_pendings';

    protected $fillable = [
        'user_id',
        'gender',
        'interest',
        'emoji',
        'language',
        'platform_id',
        'is_premium',
        'is_safe_mode',
    ];

    protected $casts = [
        'gender' => 'integer',
        'interest' => 'integer',
        'is_premium' => 'boolean',
        'is_safe_mode' => 'boolean',
        'platform_id' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
