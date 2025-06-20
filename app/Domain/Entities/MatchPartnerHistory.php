<?php

namespace App\Domain\Entities;

use Illuminate\Database\Eloquent\Model;

class MatchPartnerHistory extends Model
{
    protected $table = 'match_partner_histories';
    protected $fillable = [
        'user_id', 'match_user_id', 'expired_at'
    ];
} 
