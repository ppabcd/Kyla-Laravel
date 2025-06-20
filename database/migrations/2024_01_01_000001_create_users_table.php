<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('telegram_id')->unique();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('username')->nullable();
            $table->string('language_code', 10)->default('en');
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->enum('interest', ['male', 'female'])->nullable();
            $table->integer('age')->nullable();
            $table->string('location')->nullable();
            $table->boolean('is_premium')->default(false);
            $table->boolean('is_banned')->default(false);
            $table->boolean('is_searching')->default(false);
            $table->text('banned_reason')->nullable();
            $table->timestamp('premium_expires_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamp('banned_at')->nullable();
            $table->json('settings')->nullable();
            $table->json('metadata')->nullable();
            $table->integer('balance')->default(0);
            $table->timestamps();

            $table->index(['gender', 'interest']);
            $table->index('is_banned');
            $table->index('is_premium');
            $table->index('last_activity_at');
        });
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
