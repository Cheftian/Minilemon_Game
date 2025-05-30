<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration {
    public function up() {
        Schema::create('users', function (Blueprint $table) {
            $table->id('User_ID');
            $table->string('Username', 50);
            $table->string('Email', 100)->unique();
            $table->string('Password');
            $table->string('Profile_Image')->nullable();
            $table->unsignedBigInteger('Character_ID')->nullable();

            $table->foreign('Character_ID')->references('Character_ID')->on('characters')->onDelete('set null');
        });
    }

    public function down() {
        Schema::dropIfExists('users');        
    }
}
