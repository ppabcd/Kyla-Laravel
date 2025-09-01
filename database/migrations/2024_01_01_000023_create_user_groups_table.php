<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_groups', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_user')->unique();
            $table->string('first_name', 200)->nullable();
            $table->string('last_name', 200)->nullable();
            $table->string('username', 200)->nullable();
            $table->integer('gender')->nullable();
            $table->string('gender_icon', 10)->nullable();
            $table->integer('interest')->nullable();
            $table->string('language', 10)->nullable();
            $table->integer('premium')->default(0);
            $table->integer('banned')->default(0);
            $table->integer('is_auto')->default(0);
            $table->integer('platform_id')->nullable();
            $table->timestamps();

            $table->index('id_user', 'users_id_uindex');
            $table->index('gender');
            $table->index('interest');
            $table->index('premium');
            $table->index('banned');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_groups');
    }
};
