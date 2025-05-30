<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Space extends Model {
    protected $table = 'spaces';
    protected $primaryKey = 'Spaces_ID';
    public $timestamps = false;

protected $fillable = [
    'Name', 'Code', 'Password', 'Banner_Image'
];

}
