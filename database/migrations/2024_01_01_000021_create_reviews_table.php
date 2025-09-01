<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('group_id')->nullable();
            $table->text('caption')->nullable();
            $table->bigInteger('message_id')->nullable();
            $table->string('link', 255)->nullable();
            $table->string('type', 225)->nullable();
            $table->string('file_id', 100)->nullable();
            $table->string('file_unique_id', 50)->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('group_id');
            $table->index('message_id');
            $table->index('file_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
