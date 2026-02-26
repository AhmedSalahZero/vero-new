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
        Schema::connection('non_banking_service')->create('equity_opening_balances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->json('dividend_statement')->nullable();
            $table->decimal('paid_up_capital_amount', 14)->default(0);
            $table->longText('legal_reserve_extended')->nullable();
            $table->longText('paid_up_capital_extended')->nullable();
            $table->decimal('legal_reserve', 14)->default(0);
            $table->decimal('retained_earnings', 14)->default(0);
            $table->unsignedBigInteger('study_id');
            $table->timestamps();
            $table->longText('statement')->nullable()->comment('(DC2Type:json)');
            $table->json('retained_earning_distribution_amounts')->nullable();
            $table->json('retained_earning_distribution_payments')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('non_banking_service')->dropIfExists('equity_opening_balances');
    }
};
