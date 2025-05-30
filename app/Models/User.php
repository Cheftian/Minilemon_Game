<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

class User extends Model {
    protected $table = 'users';
    protected $primaryKey = 'User_ID';
    public $timestamps = false;

    protected $fillable = [
        'Username', 'Email', 'Password', 'Character_ID', 'api_token', 'Profile_Image'
    ];

    protected $hidden = ['Password', 'api_token'];


    public function character() {
        return $this->belongsTo(Character::class, 'Character_ID', 'Character_ID');
    }

    public function getAuthIdentifierName()
    {
        return 'User_ID';
    }
}
