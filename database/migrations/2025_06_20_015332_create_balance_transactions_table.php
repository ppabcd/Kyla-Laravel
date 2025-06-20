<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('balance_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->enum('type', ['credit', 'debit']);
            $table->decimal('amount', 10, 2);
            $table->decimal('current_balance', 10, 2);
            $table->string('description');
            $table->string('reference_id')->nullable();
            $table->string('reference_type')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])->default('completed');
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'type']);
            $table->index(['type', 'status']);
            $table->index('created_at');
            $table->index('reference_id');

            // Foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('balance_transactions');
    }
};
