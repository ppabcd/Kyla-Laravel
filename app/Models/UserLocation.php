<?php

namespace App\Models;

use Database\Factories\UserLocationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLocation extends Model
{
    /** @use HasFactory<UserLocationFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'lat',
        'lon',
        'city',
        'age',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
