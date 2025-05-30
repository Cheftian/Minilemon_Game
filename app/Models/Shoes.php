<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shoes extends Model {
    protected $table = 'Shoes';
    protected $primaryKey = 'Shoes_ID';
    public $timestamps = false;

    protected $fillable = ['Name', 'Image_URL'];
}
