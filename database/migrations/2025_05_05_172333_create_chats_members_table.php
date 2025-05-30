<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatsMembersTable extends Migration
{
    public function up()
    {
        Schema::create('chats_members', function (Blueprint $table) {
            $table->id('ChatsMember_ID');
            $table->unsignedBigInteger('Chats_ID');
            $table->unsignedBigInteger('User_ID');
            $table->timestamps();

            $table->foreign('Chats_ID')->references('Chats_ID')->on('chats')->onDelete('cascade');
            $table->foreign('User_ID')->references('User_ID')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('chats_members');
    }
}
