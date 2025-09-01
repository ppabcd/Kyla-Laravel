<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pairs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('partner_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['active', 'ended', 'blocked'])->default('active');
            $table->boolean('active')->default(true);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->foreignId('ended_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('ended_reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['partner_id', 'status']);
            $table->index('status');
            $table->index('active');
            $table->index('started_at');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pairs');
    }
};
