<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClothesTable extends Migration {
    public function up() {
        Schema::create('Skin', function (Blueprint $table) {
            $table->id('Skin_ID');
            $table->string('Name');
            $table->string('Image_URL')->nullable();
        });
        Schema::create('Shoes', function (Blueprint $table) {
            $table->id('Shoes_ID');
            $table->string('Name');
            $table->string('Image_URL')->nullable();
        });
        Schema::create('Top', function (Blueprint $table) {
            $table->id('Top_ID');
            $table->string('Name');
            $table->string('Image_URL')->nullable();
        });
        Schema::create('Hair', function (Blueprint $table) {
            $table->id('Hair_ID');
            $table->string('Name');
            $table->string('Image_URL')->nullable();
        });
        Schema::create('Bottom', function (Blueprint $table) {
            $table->id('Bottom_ID');
            $table->string('Name');
            $table->string('Image_URL')->nullable();
        });
        Schema::create('Accessory', function (Blueprint $table) {
            $table->id('Accessory_ID');
            $table->string('Name');
            $table->string('Image_URL')->nullable();
        });
        Schema::create('Jacket', function (Blueprint $table) {
            $table->id('Jacket_ID');
            $table->string('Name');
            $table->string('Image_URL')->nullable();
        });
        Schema::create('ClothesInSet', function (Blueprint $table) {
            $table->id('ClothesInSet_ID');
            $table->string('Name');
            $table->string('Image_URL')->nullable();
        });
    }

    public function down() {
        Schema::dropIfExists('Skin');
        Schema::dropIfExists('Shoes');
        Schema::dropIfExists('Top');
        Schema::dropIfExists('Hair');
        Schema::dropIfExists('Bottom');
        Schema::dropIfExists('Accessory');
        Schema::dropIfExists('Jacket');
        Schema::dropIfExists('ClothesInSet');
    }
}
