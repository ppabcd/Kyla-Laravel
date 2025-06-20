<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supports', function (Blueprint $table) {
            $table->id();
            $table->integer('chat_channel_id')->nullable();
            $table->integer('chat_id')->nullable();
            $table->bigInteger('user_id')->unique();
            $table->integer('cs_status')->nullable();
            $table->string('status_message', 255)->nullable();
            $table->integer('is_banned')->default(0);
            $table->timestamps();

            $table->index('user_id', 'supports_userId_uindex');
            $table->index('chat_channel_id');
            $table->index('cs_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supports');
    }
}; 
