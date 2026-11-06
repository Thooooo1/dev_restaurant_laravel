<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotMessage extends Model
{
    protected $table = 'chatbot_messages';

    protected $fillable = [
        'session_id',
        'user_id',
        'role',
        'content',
        'tool_calls',
        'tool_output',
        'metadata',
    ];

    protected $casts = [
        'tool_calls' => 'array',
        'tool_output' => 'array',
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
