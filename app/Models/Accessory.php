<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Accessory extends Model {
    protected $table = 'Accessory';
    protected $primaryKey = 'Accessory_ID';
    public $timestamps = false;

    protected $fillable = ['Name', 'Image_URL'];
}
