<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Character extends Model {
    protected $table = 'characters';
    protected $primaryKey = 'Character_ID';
    public $timestamps = false;

    protected $fillable = [
        'Skin_ID', 'Top_ID', 'Shoes_ID', 'Hair_ID',
        'Bottom_ID', 'Accessory_ID', 'Jacket_ID', 'ClothesInSet_ID'
    ];

    public function skin() {
        return $this->belongsTo(Skin::class, 'Skin_ID', 'Skin_ID');
    }

    public function top() {
        return $this->belongsTo(Top::class, 'Top_ID', 'Top_ID');
    }

    public function shoes() {
        return $this->belongsTo(Shoes::class, 'Shoes_ID', 'Shoes_ID');
    }

    public function hair() {
        return $this->belongsTo(Hair::class, 'Hair_ID', 'Hair_ID');
    }

    public function bottom() {
        return $this->belongsTo(Bottom::class, 'Bottom_ID', 'Bottom_ID');
    }

    public function accessory() {
        return $this->belongsTo(Accessory::class, 'Accessory_ID', 'Accessory_ID');
    }

    public function jacket() {
        return $this->belongsTo(Jacket::class, 'Jacket_ID', 'Jacket_ID');
    }

    public function clothesInSet() {
        return $this->belongsTo(ClothesInSet::class, 'ClothesInSet_ID', 'ClothesInSet_ID');
    }
}
