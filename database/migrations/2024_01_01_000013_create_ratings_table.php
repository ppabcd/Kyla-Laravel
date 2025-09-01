<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->bigInteger('rated_user_id');
            $table->integer('rating');
            $table->timestamps();

            $table->unique(['user_id', 'rated_user_id'], 'rating_pk_unique');
            $table->index('user_id');
            $table->index('rated_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
