<?php

namespace App\Domain\Entities;

use Illuminate\Database\Eloquent\Model;

class ConversationLog extends Model
{
    protected $table = 'conversation_logs';
    protected $fillable = [
        'conv_id', 'user_id', 'chat_id', 'message_id', 'is_action'
    ];
} 
