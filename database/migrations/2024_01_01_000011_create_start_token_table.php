<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('start_token', function (Blueprint $table) {
            $table->id();
            $table->string('token')->unique();
            $table->integer('type');
            $table->bigInteger('target_id');
            $table->timestamps();

            $table->unique(['token', 'target_id'], 'start_token_token_targetId_key');
            $table->unique(['type', 'target_id'], 'start_token_type_targetId_key');
            $table->index('token');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('start_token');
    }
};
