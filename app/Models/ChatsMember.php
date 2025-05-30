<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatsMember extends Model
{
    use HasFactory;

    protected $table = 'chats_members';
    protected $primaryKey = 'ChatsMember_ID';

    protected $fillable = [
        'Chats_ID',
        'User_ID',
    ];

    public function chat()
    {
        return $this->belongsTo(Chat::class, 'Chats_ID');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'User_ID');
    }
}
