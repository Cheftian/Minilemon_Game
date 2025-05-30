<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPosition extends Model {
    protected $table = 'user_position';
    protected $primaryKey = 'Position_ID';
    public $timestamps = false;

    protected $fillable = [
        'SpacesMember_ID', 'ChatArea_ID','Room_ID', 'PosX', 'PosY', 'FacingDirection', 'LastUpdated'
    ];

    public function space_member() {
        return $this->belongsTo(SpacesMember::class, 'SpacesMember_ID');
    }

    public function room() {
        return $this->belongsTo(Room::class, 'Room_ID');
    }

    public function chat_area() {
    return $this->belongsTo(ChatArea::class, 'ChatArea_ID');
}

}
