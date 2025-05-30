<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpacesMemberTable extends Migration {
    public function up() {
        Schema::create('spaces_member', function (Blueprint $table) {
            $table->id('SpacesMember_ID');
            $table->string('Role');
            $table->boolean('Online')->default(false);
            $table->boolean('Active_Video')->default(false);
            $table->boolean('Active_Mic')->default(false);

            $table->unsignedBigInteger('User_ID');
            $table->unsignedBigInteger('Spaces_ID');

            $table->foreign('User_ID')->references('User_ID')->on('users')->onDelete('cascade');
            $table->foreign('Spaces_ID')->references('Spaces_ID')->on('spaces')->onDelete('cascade');
        });
    }

    public function down() {
        Schema::dropIfExists('spaces_member');
    }
}
