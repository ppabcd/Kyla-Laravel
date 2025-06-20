<?php

namespace App\Domain\Entities;

use Illuminate\Database\Eloquent\Model;

class MatchPictures extends Model
{
    protected $table = 'match_pictures';
    protected $fillable = [
        'user_id', 'path', 'file_id', 'file_id_alive', 'url', 'e_tag', 'is_active'
    ];
} 
