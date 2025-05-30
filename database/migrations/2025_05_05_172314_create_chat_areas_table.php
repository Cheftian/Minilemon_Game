<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatAreasTable extends Migration
{
    public function up()
    {
        Schema::create('chat_areas', function (Blueprint $table) {
            $table->id('ChatArea_ID');
            $table->unsignedBigInteger('Room_ID');
            $table->enum('Area_Type', ['group_chat', 'private_chat']);
            $table->boolean('Temporary')->default(true);
            $table->unsignedBigInteger('Objects_ID');
            $table->timestamps();

            $table->foreign('Room_ID')->references('Room_ID')->on('room')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('chat_areas');
    }
}
