<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClothesInSet extends Model {
    protected $table = 'ClothesInSet';
    protected $primaryKey = 'ClothesInSet_ID';
    public $timestamps = false;

    protected $fillable = ['Name', 'Image_URL'];
}
