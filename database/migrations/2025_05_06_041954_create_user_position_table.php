<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserPositionTable extends Migration {
    public function up() {
        Schema::create('user_position', function (Blueprint $table) {
            $table->id('Position_ID');
            $table->unsignedBigInteger('SpacesMember_ID');
            $table->unsignedBigInteger('Room_ID');
            $table->unsignedBigInteger('ChatArea_ID')->nullable();
            $table->integer('PosX');
            $table->integer('PosY');
            $table->string('FacingDirection')->default('down');
            $table->timestamp('LastUpdated')->useCurrent();

            $table->foreign('SpacesMember_ID')->references('SpacesMember_ID')->on('spaces_member')->onDelete('cascade');
            $table->foreign('Room_ID')->references('Room_ID')->on('room')->onDelete('cascade');
            $table->foreign('ChatArea_ID')->references('ChatArea_ID')->on('chat_areas')->onDelete('set null');
        });

    }

    public function down() {
        Schema::dropIfExists('user_position');
    }
}
