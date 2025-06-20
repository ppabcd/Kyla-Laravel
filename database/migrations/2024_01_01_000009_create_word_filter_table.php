<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('word_filter', function (Blueprint $table) {
            $table->id();
            $table->string('word', 200);
            $table->integer('word_type');
            $table->boolean('is_open_ai_check')->default(false);
            $table->timestamps();

            $table->unique(['word', 'word_type'], 'word_filter_pk2');
            $table->index('word');
            $table->index('word_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('word_filter');
    }
}; 
