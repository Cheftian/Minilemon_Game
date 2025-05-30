<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatArea extends Model
{
    protected $table = 'chat_areas';
    protected $primaryKey = 'ChatArea_ID';
    protected $fillable = ['Room_ID', 'Area_Type', 'Temporary', 'Objects_ID'];

    public function room()
    {
        return $this->belongsTo(Room::class, 'Room_ID');
    }
}
