<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'safe_mode')) {
                $table->boolean('safe_mode')->default(false)->after('is_searching');
            }
            if (! Schema::hasColumn('users', 'last_message_at')) {
                $table->timestamp('last_message_at')->nullable()->after('last_activity_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'safe_mode')) {
                $table->dropColumn('safe_mode');
            }
            if (Schema::hasColumn('users', 'last_message_at')) {
                $table->dropColumn('last_message_at');
            }
        });
    }
};
