<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Only create if table doesn't exist
        Schema::create('media_sender', function (Blueprint $table) {
            $table->id();
            $table->string('file_unique_id', 200);
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->unique(['file_unique_id', 'user_id']);
            $table->foreign('file_unique_id')->references('file_unique_id')->on('media')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_sender');
    }
};
