<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpacesMember extends Model {
    protected $table = 'spaces_member';
    protected $primaryKey = 'SpacesMember_ID';
    public $timestamps = false;

    protected $fillable = [
        'Role', 'Online', 'Active_Video', 'Active_Mic', 'User_ID', 'Spaces_ID'
    ];

    protected $casts = [
        'Online' => 'boolean',
        'Active_Video' => 'boolean',
        'Active_Mic' => 'boolean',
    ];

    public function user() {
        return $this->belongsTo(User::class, 'User_ID', 'User_ID');
    }

    public function space() {
        return $this->belongsTo(Space::class, 'Spaces_ID', 'Spaces_ID');
    }
}
