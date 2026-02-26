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
        Schema::connection('non_banking_service')->create('income_statement_reports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->longText('total_admin_fees')->nullable();
            $table->longText('existing_interests_revenues')->nullable();
            $table->longText('existing_interests_expense')->nullable();
            $table->longText('existing_loans_interests_expense')->nullable();
            $table->longText('fixed_asset_loan_interest_expenses')->nullable();
            $table->longText('securitization_reverse_interest_revenues')->nullable();
            $table->longText('securitization_reverse_loan_interest_expense')->nullable();
            $table->longText('securitization_collection_revenues')->nullable();
            $table->longText('securitization_early_settlement_expense')->nullable();
            $table->longText('securitization_expense')->nullable();
            $table->longText('securitization_gain_or_loss')->nullable();
            $table->longText('leasing_revenue')->nullable();
            $table->longText('leasing_bank_interest')->nullable();
            $table->longText('direct-factoring_revenue')->nullable();
            $table->longText('direct-factoring_bank_interest')->nullable();
            $table->longText('reverse-factoring_revenue')->nullable();
            $table->longText('reverse-factoring_bank_interest')->nullable();
            $table->longText('ijara_revenue')->nullable();
            $table->longText('ijara_bank_interest')->nullable();
            $table->longText('portfolio-mortgage_revenue')->nullable();
            $table->longText('portfolio-mortgage_bank_interest')->nullable();
            $table->longText('microfinance_revenue')->nullable();
            $table->longText('consumer-finance_revenue')->nullable();
            $table->longText('microfinance_bank_interest')->nullable();
            $table->longText('consumer-finance_bank_interest')->nullable();
            $table->longText('manpower_expenses')->nullable();
            $table->longText('total_manpower_expenses')->nullable();
            $table->longText('existing_ecl_expenses')->nullable();
            $table->json('non_performing_existing_ecl_expenses')->nullable();
            $table->longText('ecl_expenses')->nullable();
            $table->longText('depreciation_expenses')->nullable();
            $table->longText('opening_depreciation_expenses')->nullable();
            $table->longText('oda_interests')->nullable()->comment('(DC2Type:json)');
            $table->longText('cost-of-service')->nullable();
            $table->longText('total_cost-of-service')->nullable();
            $table->longText('marketing-expense')->nullable();
            $table->longText('total_marketing-expense')->nullable();
            $table->longText('other-operation-expense')->nullable();
            $table->longText('total_other-operation-expense')->nullable();
            $table->longText('sales-expense')->nullable();
            $table->longText('total_sales-expense')->nullable();
            $table->longText('general-expense')->nullable();
            $table->longText('total_general-expense')->nullable();
            $table->longText('corporate_taxes')->nullable();
            $table->unsignedBigInteger('study_id');
            $table->unsignedBigInteger('company_id');
            $table->timestamps();
            $table->longText('interest_cash_surplus')->nullable();
            $table->longText('corporate_taxes_end_balance')->nullable();
            $table->json('interest_corridor_changes')->nullable();
            $table->json('loans_interest_corridor_changes')->nullable();
            $table->json('right_of_user_amortization')->nullable();
            $table->json('rent_interest')->nullable();
            $table->json('new_branches_rent_interest')->nullable();
            $table->json('new_branches_rent_amortization')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('non_banking_service')->dropIfExists('income_statement_reports');
    }
};
