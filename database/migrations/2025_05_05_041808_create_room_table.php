<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoomTable extends Migration {
    public function up() {
        Schema::create('room', function (Blueprint $table) {
            $table->id('Room_ID');
            $table->unsignedBigInteger('Spaces_ID');
            $table->string('Room_Name');

            $table->foreign('Spaces_ID')->references('Spaces_ID')->on('spaces')->onDelete('cascade');
        });
    }

    public function down() {
        Schema::dropIfExists('room');
    }
}
