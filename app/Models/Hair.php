<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hair extends Model {
    protected $table = 'Hair';
    protected $primaryKey = 'Hair_ID';
    public $timestamps = false;

    protected $fillable = ['Name', 'Image_URL'];
}
