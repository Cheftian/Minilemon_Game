<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bottom extends Model {
    protected $table = 'Bottom';
    protected $primaryKey = 'Bottom_ID';
    public $timestamps = false;

    protected $fillable = ['Name', 'Image_URL'];
}
