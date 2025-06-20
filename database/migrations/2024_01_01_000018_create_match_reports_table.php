<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('match_reports', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('reporter_id');
            $table->bigInteger('target_id');
            $table->integer('report_type');
            $table->timestamps();

            $table->unique(['reporter_id', 'target_id'], 'match_reports_pk');
            $table->index('reporter_id', 'match_reports_reporterId_index');
            $table->index('target_id', 'match_reports_targetId_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('match_reports');
    }
}; 
