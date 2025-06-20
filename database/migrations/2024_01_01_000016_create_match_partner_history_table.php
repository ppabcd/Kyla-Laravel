<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('match_partner_histories', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->bigInteger('match_user_id')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('match_user_id');
            $table->index('expired_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('match_partner_histories');
    }
}; 
