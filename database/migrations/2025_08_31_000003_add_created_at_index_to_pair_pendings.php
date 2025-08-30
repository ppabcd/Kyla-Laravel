<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pair_pendings', function (Blueprint $table) {
            // Add index to support FIFO ordering efficiently
            $table->index('created_at', 'pair_pendings_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('pair_pendings', function (Blueprint $table) {
            $table->dropIndex('pair_pendings_created_at_index');
        });
    }
};
