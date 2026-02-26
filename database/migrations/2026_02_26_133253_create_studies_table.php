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
        Schema::connection('non_banking_service')->create('studies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->decimal('revenue_multiplier', 14)->default(0);
            $table->decimal('ebitda_multiplier', 14)->default(0);
            $table->decimal('cost_of_equity_rate', 14)->default(0);
            $table->string('name')->comment('اسم الدراسة');
            $table->unsignedBigInteger('existing_branches_counts')->default(0);
            $table->string('company_nature')->comment('نوع الشركة ');
            $table->unsignedBigInteger('to_be_consolidated_from_study_id')->nullable()->comment('هيكون رقم الدراسة اللي هيختارها');
            $table->date('study_start_date');
            $table->integer('duration_in_years');
            $table->date('study_end_date');
            $table->double('operation_start_month');
            $table->date('operation_start_date');
            $table->string('financial_year_start_month');
            $table->decimal('corporate_taxes_rate', 14)->default(0);
            $table->decimal('salary_taxes_rate', 14)->default(0);
            $table->decimal('social_insurance_rate', 14)->default(0);
            $table->decimal('perpetual_growth_rate', 14)->default(0);
            $table->decimal('shareholder_equity_multiplier', 14)->default(0);
            $table->longText('operation_dates')->nullable();
            $table->longText('study_dates')->nullable();
            $table->unsignedInteger('consumerfinance_loan_officer_count')->default(0);
            $table->unsignedInteger('consumerfinance_branches_count')->default(0);
            $table->unsignedInteger('microfinance_loan_officer_count')->default(0);
            $table->longText('microfinance_branch_ids')->nullable()->comment('(DC2Type:json)');
            $table->unsignedBigInteger('company_id');
            $table->timestamps();
            $table->boolean('has_leasing')->default(false);
            $table->boolean('has_direct_factoring')->default(false);
            $table->boolean('has_reverse_factoring')->default(false);
            $table->boolean('has_ijara_mortgage')->default(false);
            $table->boolean('has_portfolio_mortgage')->default(false);
            $table->boolean('has_micro_finance')->default(false);
            $table->string('microfinance_type')->nullable();
            $table->string('microfinance_no_branches')->nullable();
            $table->boolean('has_securitization')->default(false);
            $table->boolean('has_consumer_finance')->default(false);
            $table->unsignedBigInteger('deleted_it')->nullable();
            $table->unsignedBigInteger('deleted_it2')->nullable();
            $table->longText('leasing_growth_rates')->nullable();
            $table->integer('microfinance_product_mix_count')->default(1);
            $table->string('microfinance_product_mix_or_existing_branch')->nullable();
            $table->longText('product_mix_senior_loan_officers')->nullable();
            $table->longText('product_mix_loan_officers')->nullable();
            $table->longText('previous_years_income_statement')->nullable();
            $table->json('right_of_use_rent')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('non_banking_service')->dropIfExists('studies');
    }
};
