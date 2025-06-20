<?php

namespace App\Domain\Entities;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $table = 'media';
    protected $fillable = [
        'file_unique_id',
        'is_blocked',
    ];
} 
