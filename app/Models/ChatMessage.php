<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasFactory;

    protected $table = 'chat_messages';
    protected $primaryKey = 'Message_ID';

    protected $fillable = [
        'Chats_ID',
        'ChatsMember_ID',
        'Message',
        'Time',
    ];

    public $timestamps = false;

    public function chat()
    {
        return $this->belongsTo(Chat::class, 'Chats_ID');
    }

    public function user()
    {
        return $this->belongsTo(ChatsMember::class, 'ChatsMember_ID');
    }
}
