<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('violations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('content');
            $table->string('violation_type'); // promotion, spam, inappropriate, harassment, etc.
            $table->integer('severity')->default(1); // 1=low, 2=medium, 3=high
            $table->string('action_taken')->nullable(); // soft_ban, warning, permanent_ban, etc.
            $table->integer('ban_duration_minutes')->nullable();
            $table->timestamp('detected_at');
            $table->timestamps();

            $table->index(['user_id', 'detected_at']);
            $table->index(['violation_type', 'detected_at']);
            $table->index(['action_taken', 'detected_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('violations');
    }
};
