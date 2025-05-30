<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Skin extends Model {
    protected $table = 'Skin';
    protected $primaryKey = 'Skin_ID';
    public $timestamps = false;

    protected $fillable = ['Name', 'Image_URL'];
}
