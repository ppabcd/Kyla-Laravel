<?php

namespace App\Domain\Entities;

use Illuminate\Database\Eloquent\Model;

class Reviews extends Model
{
    protected $table = 'reviews';
    protected $fillable = [
        'user_id', 'group_id', 'caption', 'message_id', 'link', 'type', 'file_id', 'file_unique_id'
    ];
} 
