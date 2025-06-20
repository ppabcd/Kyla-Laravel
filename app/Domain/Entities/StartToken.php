<?php

namespace App\Domain\Entities;

use Illuminate\Database\Eloquent\Model;

class StartToken extends Model
{
    protected $table = 'start_token';
    protected $fillable = [
        'token', 'type', 'target_id'
    ];
} 
