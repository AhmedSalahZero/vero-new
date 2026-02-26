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
        Schema::connection('property_management')->create('cashflow_statement_reports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->json('total_admin_fees')->nullable();
            $table->longText('opening_dividend_payments')->nullable();
            $table->integer('opening_cash')->nullable()->default(0);
            $table->longText('other_long_term_asset_collections')->nullable();
            $table->longText('total_other_long_term_asset_collections')->nullable();
            $table->longText('ffe_loan_withdrawal')->nullable();
            $table->longText('existing_other_debtors_collection')->nullable();
            $table->longText('total_existing_other_debtors_collection')->nullable();
            $table->longText('existing_other_creditors_payment')->nullable();
            $table->longText('total_existing_other_creditors_payment')->nullable();
            $table->longText('existing_long_term_loans_payment')->nullable();
            $table->longText('total_existing_long_term_loans_payment')->nullable();
            $table->longText('existing_other_long_term_liabilities_payment')->nullable();
            $table->longText('total_existing_other_long_term_liabilities_payment')->nullable();
            $table->longText('fixed_asset_loan_schedule_payments')->nullable();
            $table->longText('fixed_asset_payments')->nullable();
            $table->longText('expense_payments')->nullable();
            $table->longText('total_expense_payments')->nullable();
            $table->longText('salary_payments')->nullable();
            $table->longText('salary_tax_social_insurance_payments')->nullable();
            $table->longText('withhold_payments')->nullable();
            $table->longText('corporate_taxes_payments')->nullable();
            $table->longText('corporate_taxes_end_balances')->nullable();
            $table->unsignedBigInteger('study_id');
            $table->unsignedBigInteger('company_id');
            $table->timestamps();
            $table->longText('total_fixed_asset_replacement_costs')->nullable();
            $table->longText('oda_statements')->nullable();
            $table->longText('extra_capital_injection')->nullable();
            $table->longText('manual_capital_injection')->nullable();
            $table->longText('cash_opening_balances')->nullable();
            $table->longText('cash_and_bank_beginning_balances')->nullable();
            $table->longText('cash_end_balances')->nullable();
            $table->boolean('has_manual_equity_injection')->default(false);
            $table->longText('manual_equity_injection')->nullable();
            $table->longText('net_cash_before_extra_capital_injection')->nullable();
            $table->json('full_coverage_rent_collections')->nullable();
            $table->json('partial_coverage_rent_collections')->nullable();
            $table->json('to_be_delivered_rent_collections')->nullable();
            $table->json('property_forecasted_rent_collections')->nullable();
            $table->json('existing_properties_installments')->nullable();
            $table->json('new_properties_installments')->nullable();
            $table->json('existing_portfolio_loans_payment')->nullable();
            $table->json('existing_portfolio_collection')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('property_management')->dropIfExists('cashflow_statement_reports');
    }
};
