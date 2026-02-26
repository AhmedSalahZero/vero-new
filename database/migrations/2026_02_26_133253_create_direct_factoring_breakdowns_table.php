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
        Schema::connection('non_banking_service')->create('direct_factoring_breakdowns', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('category');
            $table->decimal('margin_rate', 14);
            $table->longText('percentage_payload')->nullable();
            $table->longText('loan_amounts')->nullable();
            $table->longText('monthly_loan_amounts')->nullable();
            $table->longText('disbursement_amounts')->nullable();
            $table->longText('beginning_balance')->nullable();
            $table->longText('interest_revenue')->nullable();
            $table->longText('unearned_interest')->nullable();
            $table->longText('end_balance')->nullable();
            $table->longText('net_funding_amounts')->nullable();
            $table->longText('statement_beginning_balance')->nullable();
            $table->longText('direct_factoring_amounts')->nullable();
            $table->longText('direct_factoring_settlements')->nullable();
            $table->longText('statement_end_balance')->nullable();
            $table->longText('bank_beginning_balance')->nullable();
            $table->longText('bank_loan_amounts')->nullable();
            $table->longText('bank_loan_settlements')->nullable();
            $table->longText('bank_interest_expense_payments')->nullable();
            $table->longText('bank_total_dues')->nullable();
            $table->longText('bank_interest_expense')->nullable();
            $table->longText('bank_end_balance')->nullable();
            $table->unsignedBigInteger('study_id');
            $table->unsignedBigInteger('company_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('non_banking_service')->dropIfExists('direct_factoring_breakdowns');
    }
};
