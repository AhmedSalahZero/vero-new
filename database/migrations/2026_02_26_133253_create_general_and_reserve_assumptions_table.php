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
        Schema::connection('non_banking_service')->create('general_and_reserve_assumptions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('study_id')->index('general_and_reserve_assumptions_study_id_foreign');
            $table->decimal('legal_reserve_rate', 14)->default(0);
            $table->decimal('max_legal_reserve_rate', 14)->default(0);
            $table->decimal('financial_regulatory_authority_rate', 14)->default(0);
            $table->decimal('max_financial_regulatory_authority_rate', 14)->default(0);
            $table->longText('employee_profit_share_rates');
            $table->longText('border_of_directors_profit_share_rates');
            $table->longText('shareholders_first_dividend_portions');
            $table->longText('shareholders_dividend_payout_ratios');
            $table->longText('shareholders_dividend_in_cash_or_shares');
            $table->longText('salaries_annual_increase_rates')->nullable()->comment('(DC2Type:json)');
            $table->longText('cbe_lending_corridor_rates');
            $table->json('cbe_base_lending_corridor_rates')->nullable();
            $table->json('cbe_corridor_changes_rates')->nullable();
            $table->longText('bank_lending_margin_rates');
            $table->longText('odas_bank_lending_margin_rates')->nullable();
            $table->longText('credit_interest_rate_for_surplus_cash');
            $table->unsignedBigInteger('company_id');
            $table->timestamps();
            $table->unsignedInteger('from_dispersement_of');
            $table->json('from_dispersement_of_rates')->nullable();
            $table->unsignedInteger('to_cover_cost');
            $table->json('to_cover_cost_rates')->nullable();
            $table->json('min_cash_balances')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('non_banking_service')->dropIfExists('general_and_reserve_assumptions');
    }
};
