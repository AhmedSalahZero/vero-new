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
        Schema::connection('non_banking_service')->create('cashflow_statement_reports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->json('total_admin_fees')->nullable();
            $table->json('opening_dividend_payments')->nullable();
            $table->longText('microfinance_disbursements')->nullable();
            $table->longText('portfolio-mortgage_disbursements')->nullable();
            $table->longText('reverse-factoring_disbursements')->nullable();
            $table->longText('ijara_disbursements')->nullable();
            $table->longText('direct-factoring_disbursements')->nullable();
            $table->longText('leasing_disbursements')->nullable();
            $table->integer('opening_cash')->nullable()->default(0);
            $table->longText('other_long_term_asset_collections')->nullable();
            $table->longText('total_other_long_term_asset_collections')->nullable();
            $table->longText('ffe_loan_withdrawal')->nullable();
            $table->longText('securitization_reverse_collection')->nullable();
            $table->longText('securitization_collection_revenues')->nullable();
            $table->longText('securitization_reverse_loan_payment')->nullable();
            $table->longText('securitization_npv')->nullable();
            $table->longText('securitization_bank_settlement')->nullable();
            $table->longText('securitization_early_settlement_expense')->nullable();
            $table->longText('securitization_expense')->nullable();
            $table->longText('leasing_collection')->nullable();
            $table->longText('leasing_loan_withdrawal_amount')->nullable();
            $table->longText('leasing_payment')->nullable();
            $table->longText('direct-factoring_collection')->nullable();
            $table->longText('direct-factoring_loan_withdrawal_amount')->nullable();
            $table->longText('direct-factoring_payment')->nullable();
            $table->longText('direct-factoring_bank_interest')->nullable();
            $table->longText('reverse-factoring_collection')->nullable();
            $table->longText('reverse-factoring_loan_withdrawal_amount')->nullable();
            $table->longText('reverse-factoring_payment')->nullable();
            $table->longText('ijara_collection')->nullable();
            $table->longText('ijara_loan_withdrawal_amount')->nullable();
            $table->longText('ijara_payment')->nullable();
            $table->longText('portfolio-mortgage_collection')->nullable();
            $table->longText('portfolio-mortgage_loan_withdrawal_amount')->nullable();
            $table->longText('consumer-finance_loan_withdrawal_amount')->nullable();
            $table->longText('portfolio-mortgage_payment')->nullable();
            $table->longText('microfinance_collection')->nullable();
            $table->longText('consumer-finance_collection')->nullable();
            $table->longText('microfinance_loan_withdrawal_amount')->nullable();
            $table->longText('microfinance_payment')->nullable();
            $table->longText('consumer-finance_payment')->nullable();
            $table->longText('existing_portfolio_collection')->nullable();
            $table->longText('existing_other_debtors_collection')->nullable();
            $table->longText('total_existing_other_debtors_collection')->nullable();
            $table->longText('existing_portfolio_loans_payment')->nullable();
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
            $table->longText('consumer-finance_oda_withdrawals')->nullable();
            $table->longText('microfinance_oda_withdrawals')->nullable();
            $table->longText('oda_withdrawals')->nullable();
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
            $table->json('net_cash_before_extra_capital_injection')->nullable();
            $table->json('leasing_portfolio_interest_change_collections')->nullable();
            $table->json('leasing_bank_portfolio_interest_change_payments')->nullable();
            $table->json('interest_corridor_changes')->nullable();
            $table->json('loans_interest_corridor_changes')->nullable();
            $table->json('rent_payment')->nullable();
            $table->json('new_branches_rent_payments')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('non_banking_service')->dropIfExists('cashflow_statement_reports');
    }
};
