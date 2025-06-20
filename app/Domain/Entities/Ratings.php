<?php

namespace App\Domain\Entities;

use Illuminate\Database\Eloquent\Model;

class Ratings extends Model
{
    protected $table = 'ratings';
    protected $fillable = [
        'user_id', 'rated_user_id', 'rating'
    ];
} 
