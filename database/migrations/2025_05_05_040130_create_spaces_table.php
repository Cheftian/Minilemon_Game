<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpacesTable extends Migration {
    public function up() {
        Schema::create('spaces', function (Blueprint $table) {
            $table->id('Spaces_ID');
            $table->string('Name');
            $table->string('Code')->unique();
            $table->string('Password');
            $table->string('Banner_Image')->nullable();
        });
    }

    public function down() {
        Schema::dropIfExists('spaces');
    }
}
