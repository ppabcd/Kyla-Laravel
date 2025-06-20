<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('referrer_id');
            $table->bigInteger('referred_id');
            $table->timestamps();

            $table->unique(['referrer_id', 'referred_id'], 'referrals_pk');
            $table->index('referrer_id');
            $table->index('referred_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
}; 
