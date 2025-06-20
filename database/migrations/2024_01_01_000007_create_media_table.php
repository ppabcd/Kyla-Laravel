<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('file_unique_id', 50)->unique();
            $table->boolean('is_blocked')->default(false);
            $table->timestamps();

            $table->index('file_unique_id');
            $table->index('is_blocked');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
}; 
