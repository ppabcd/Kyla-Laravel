<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversation_logs', function (Blueprint $table) {
            $table->id();
            $table->string('conv_id', 200)->nullable();
            $table->bigInteger('user_id');
            $table->bigInteger('chat_id')->nullable();
            $table->bigInteger('message_id');
            $table->integer('is_action')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'chat_id', 'message_id'], 'conversation_logs_id_groupId_uindex');
            $table->index('conv_id');
            $table->index('user_id');
            $table->index('chat_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversation_logs');
    }
};
