<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatMessagesTable extends Migration
{
    public function up()
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id('Message_ID');
            $table->unsignedBigInteger('Chats_ID');
            $table->unsignedBigInteger('ChatsMember_ID'); // Untuk mengambil Username juga
            $table->text('Message');
            $table->timestamp('Time')->useCurrent();

            $table->foreign('Chats_ID')->references('Chats_ID')->on('chats')->onDelete('cascade');
            $table->foreign('ChatsMember_ID')->references('ChatsMember_ID')->on('chats_members')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('chat_messages');
    }
}
