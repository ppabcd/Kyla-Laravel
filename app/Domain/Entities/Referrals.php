<?php

namespace App\Domain\Entities;

use Illuminate\Database\Eloquent\Model;

class Referrals extends Model
{
    protected $table = 'referrals';
    protected $fillable = [
        'referrer_id', 'referred_id'
    ];
} 
