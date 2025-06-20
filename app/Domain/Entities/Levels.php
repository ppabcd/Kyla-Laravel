<?php

namespace App\Domain\Entities;

use Illuminate\Database\Eloquent\Model;

class Levels extends Model
{
    protected $table = 'levels';
    protected $fillable = [
        'id_user', 'first_name', 'last_name', 'username', 'group_id', 'level', 'xp', 'total_character', 'total_messages', 'next_chat', 'session_id'
    ];
} 
