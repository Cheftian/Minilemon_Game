<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Top extends Model {
    protected $table = 'Top';
    protected $primaryKey = 'Top_ID';
    public $timestamps = false;

    protected $fillable = ['Name', 'Image_URL'];
}
