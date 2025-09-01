<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('reporter_id');
            $table->bigInteger('user_id');
            $table->integer('score');
            // status
            $table->enum('status', ['pending', 'resolved', 'dismissed', 'archived'])->default('pending');
            $table->string('action_taken')->nullable();
            $table->string('notes')->nullable();
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->unique(['reporter_id', 'user_id'], 'reports_reporterId_userId_uindex');
            $table->index('reporter_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
