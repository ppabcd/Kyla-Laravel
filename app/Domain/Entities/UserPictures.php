<?php

namespace App\Domain\Entities;

use Illuminate\Database\Eloquent\Model;

class UserPictures extends Model
{
    protected $table = 'user_pictures';
    protected $fillable = [
        'user_id', 'path', 'file_id', 'file_id_alive', 'url'
    ];
} 
