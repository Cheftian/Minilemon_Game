<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatsTable extends Migration
{
    public function up()
    {
        Schema::create('chats', function (Blueprint $table) {
            $table->id('Chats_ID');
            $table->enum('Chat_Type', ['Personal', 'Group', 'Broadcast']);
            $table->unsignedBigInteger('Spaces_ID');
            $table->unsignedBigInteger('Room_ID')->nullable();
            $table->unsignedBigInteger('ChatArea_ID')->nullable();
            $table->boolean('Temporary')->default(true);
            $table->timestamps();

            $table->foreign('Spaces_ID')->references('Spaces_ID')->on('spaces')->onDelete('cascade');
            $table->foreign('Room_ID')->references('Room_ID')->on('room')->onDelete('cascade');
            $table->foreign('ChatArea_ID')->references('ChatArea_ID')->on('chat_areas')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('chats');
    }
}
