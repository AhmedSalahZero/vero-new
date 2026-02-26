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
        Schema::connection('property_management')->create('general_and_reserve_assumptions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('study_id')->index('general_and_reserve_assumptions_study_id_foreign');
            $table->decimal('legal_reserve_rate', 14)->default(0);
            $table->decimal('max_legal_reserve_rate', 14)->default(0);
            $table->longText('employee_profit_share_rates');
            $table->longText('border_of_directors_profit_share_rates');
            $table->longText('shareholders_first_dividend_portions');
            $table->longText('shareholders_dividend_payout_ratios');
            $table->longText('salaries_annual_increase_rates')->nullable()->comment('(DC2Type:json)');
            $table->longText('cbe_lending_corridor_rates');
            $table->longText('bank_lending_margin_rates');
            $table->longText('odas_bank_lending_margin_rates')->nullable();
            $table->longText('credit_interest_rate_for_surplus_cash');
            $table->unsignedBigInteger('company_id');
            $table->timestamps();
            $table->unsignedInteger('to_cover_cost');
            $table->longText('to_cover_cost_rates')->nullable();
            $table->longText('min_cash_balances')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('property_management')->dropIfExists('general_and_reserve_assumptions');
    }
};
