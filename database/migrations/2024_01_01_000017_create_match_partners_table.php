<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('match_partners', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unique();
            $table->bigInteger('partner_id')->unique();
            $table->integer('message_id');
            $table->decimal('distance', 10, 2)->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'partner_id']);
            $table->index('user_id');
            $table->index('partner_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('match_partners');
    }
};
