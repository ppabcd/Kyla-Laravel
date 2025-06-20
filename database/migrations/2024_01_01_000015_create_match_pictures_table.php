<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('match_pictures', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->string('path', 255)->nullable();
            $table->string('file_id', 200)->nullable();
            $table->string('file_id_alive', 200)->nullable();
            $table->string('url', 255)->nullable();
            $table->string('e_tag', 200)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('user_id');
            $table->index('file_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('match_pictures');
    }
}; 
