<?php

namespace App\Models;

use Database\Factories\ConversationLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConversationLog extends Model
{
    /** @use HasFactory<ConversationLogFactory> */
    use HasFactory;

    protected $table = 'conversation_logs';

    protected $fillable = [
        'conv_id', 'user_id', 'chat_id', 'message_id', 'is_action',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
