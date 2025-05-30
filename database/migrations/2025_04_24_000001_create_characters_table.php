<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCharactersTable extends Migration {
    public function up() {
        Schema::create('characters', function (Blueprint $table) {
            $table->id('Character_ID');
            $table->unsignedBigInteger('Skin_ID')->nullable();
            $table->unsignedBigInteger('Top_ID')->nullable();
            $table->unsignedBigInteger('Shoes_ID')->nullable();
            $table->unsignedBigInteger('Hair_ID')->nullable();
            $table->unsignedBigInteger('Bottom_ID')->nullable();
            $table->unsignedBigInteger('Accessory_ID')->nullable();
            $table->unsignedBigInteger('Jacket_ID')->nullable();
            $table->unsignedBigInteger('ClothesInSet_ID')->nullable();
        
            $table->foreign('Skin_ID')->references('Skin_ID')->on('skin')->onDelete('set null');
            $table->foreign('Top_ID')->references('Top_ID')->on('top')->onDelete('set null');
            $table->foreign('Shoes_ID')->references('Shoes_ID')->on('shoes')->onDelete('set null');
            $table->foreign('Hair_ID')->references('Hair_ID')->on('hair')->onDelete('set null');
            $table->foreign('Bottom_ID')->references('Bottom_ID')->on('bottom')->onDelete('set null');
            $table->foreign('Accessory_ID')->references('Accessory_ID')->on('accessory')->onDelete('set null');
            $table->foreign('Jacket_ID')->references('Jacket_ID')->on('jacket')->onDelete('set null');
            $table->foreign('ClothesInSet_ID')->references('ClothesInSet_ID')->on('ClothesInSet')->onDelete('set null');
        });
    }

    public function down() {
        Schema::dropIfExists('characters');
    }
}
