<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('soft_banned_until')->nullable()->after('banned_at');
            $table->string('soft_ban_reason')->nullable()->after('soft_banned_until');
            $table->integer('promotion_violation_count')->default(0)->after('soft_ban_reason');
            $table->timestamp('last_promotion_violation_at')->nullable()->after('promotion_violation_count');

            $table->index(['soft_banned_until']);
            $table->index(['promotion_violation_count', 'last_promotion_violation_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['soft_banned_until']);
            $table->dropIndex(['promotion_violation_count', 'last_promotion_violation_at']);
            $table->dropColumn([
                'soft_banned_until',
                'soft_ban_reason',
                'promotion_violation_count',
                'last_promotion_violation_at',
            ]);
        });
    }
};
