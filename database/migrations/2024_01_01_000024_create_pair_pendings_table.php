<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pair_pendings', function (Blueprint $table) {
            $table->id();
            $table->string('id_pair', 255)->unique();
            $table->integer('gender')->nullable();
            $table->integer('interest')->nullable();
            $table->text('emoji')->nullable();
            $table->string('language', 10)->default('id');
            $table->integer('platform_id')->default(1);
            $table->integer('is_premium')->default(0);
            $table->integer('is_safe_mode')->nullable();
            $table->timestamps();

            $table->index('id_pair', 'pair_pendings_id_uindex');
            $table->index(['gender', 'interest']);
            $table->index('is_premium');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pair_pendings');
    }
}; 
