<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $table = 'chats';
    protected $primaryKey = 'Chats_ID';
    protected $fillable = [
        'Chat_Type', 'Spaces_ID', 'Room_ID', 'ChatArea_ID', 'Temporary'
    ];

    public function space()
    {
        return $this->belongsTo(Space::class, 'Spaces_ID');
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'Room_ID');
    }

    public function chatArea()
    {
        return $this->belongsTo(ChatArea::class, 'ChatArea_ID');
    }
    public function members()
    {
        return $this->hasMany(ChatsMember::class, 'Chats_ID', 'Chats_ID');
    }
}
