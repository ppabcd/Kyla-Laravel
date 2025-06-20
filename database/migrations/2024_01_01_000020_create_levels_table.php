<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('levels', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_user');
            $table->string('first_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->string('username', 100)->nullable();
            $table->bigInteger('group_id')->nullable();
            $table->integer('level')->nullable();
            $table->bigInteger('xp')->nullable();
            $table->bigInteger('total_character')->nullable();
            $table->bigInteger('total_messages')->nullable();
            $table->bigInteger('next_chat')->nullable();
            $table->integer('session_id')->nullable();
            $table->timestamps();

            $table->unique(['id_user', 'group_id', 'session_id'], 'levels_id_groupId_uindex');
            $table->index('id_user');
            $table->index('group_id');
            $table->index('level');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('levels');
    }
}; 
