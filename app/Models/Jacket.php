<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jacket extends Model {
    protected $table = 'Jacket';
    protected $primaryKey = 'Jacket_ID';
    public $timestamps = false;

    protected $fillable = ['Name', 'Image_URL'];
}
