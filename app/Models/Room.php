<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model {
    protected $table = 'room';
    protected $primaryKey = 'Room_ID';
    public $timestamps = false;

    protected $fillable = [
        'Spaces_ID', 'Room_Name'
    ];

    public function space() {
        return $this->belongsTo(Space::class, 'Spaces_ID', 'Spaces_ID');
    }
}
