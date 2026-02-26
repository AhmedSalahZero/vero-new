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
        Schema::connection('property_management')->create('balance_sheets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->longText('monthly_non_currency_assets')->nullable();
            $table->longText('total_non_currency_assets')->nullable();
            $table->longText('monthly_fixed_assets')->nullable();
            $table->longText('yearly_fixed_assets')->nullable();
            $table->longText('monthly_other_long_term_assets')->nullable();
            $table->longText('yearly_other_long_term_assets')->nullable();
            $table->longText('monthly_current_assets')->nullable();
            $table->longText('total_current_assets')->nullable();
            $table->longText('monthly_cash_and_banks')->nullable();
            $table->longText('yearly_cash_and_banks')->nullable();
            $table->longText('monthly_customer_outstanding')->nullable();
            $table->longText('yearly_customer_outstanding')->nullable();
            $table->longText('monthly_other_debtors')->nullable();
            $table->longText('yearly_other_debtors')->nullable();
            $table->longText('monthly_total_assets')->nullable();
            $table->longText('yearly_total_assets')->nullable();
            $table->longText('monthly_current_liabilities')->nullable();
            $table->longText('yearly_current_liabilities')->nullable();
            $table->longText('monthly_portfolio_loan_outstanding')->nullable();
            $table->longText('yearly_portfolio_loan_outstanding')->nullable();
            $table->longText('monthly_other_creditors')->nullable();
            $table->longText('yearly_other_creditors')->nullable();
            $table->longText('monthly_long_term_liabilities')->nullable();
            $table->longText('yearly_long_term_liabilities')->nullable();
            $table->longText('monthly_shareholder_equity')->nullable();
            $table->longText('yearly_shareholder_equity')->nullable();
            $table->longText('mtls_structures')->nullable();
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
        Schema::connection('property_management')->dropIfExists('balance_sheets');
    }
};
