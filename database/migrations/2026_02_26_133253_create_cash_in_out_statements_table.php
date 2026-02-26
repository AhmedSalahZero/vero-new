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
        Schema::connection('non_banking_service')->create('cash_in_out_statements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->longText('monthly_equity_injection')->nullable();
            $table->longText('monthly_working_capital_injection')->nullable();
            $table->longText('monthly_cash_and_banks')->nullable();
            $table->unsignedBigInteger('study_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('non_banking_service')->dropIfExists('cash_in_out_statements');
    }
};
