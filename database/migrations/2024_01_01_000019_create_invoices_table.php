<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice')->unique();
            $table->bigInteger('user_id');
            $table->bigInteger('total_amount')->nullable();
            $table->string('currency')->nullable();
            $table->string('telegram_payment_charge_id')->nullable();
            $table->string('provider_payment_charge_id')->nullable();
            $table->integer('status_id')->default(0);
            $table->timestamps();

            $table->index('invoice', 'invoice_invoice_index');
            $table->index('user_id', 'invoice_userId_index');
            $table->index('status_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
}; 
