<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGeneralAndReserveAssumptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('non_banking_service')->create('general_and_reserve_assumptions', function (Blueprint $table) {
            $table->id();
			$table->unsignedBigInteger('study_id');
			 $table->foreign('study_id')->references('id')->on('studies')->cascadeOnDelete();
			$table->decimal('legal_reserve_rate',14,2)->default(0);
			$table->decimal('max_legal_reserve_rate',14,2)->default(0);
			$table->decimal('financial_regulatory_authority_rate',14,2)->default(0);
			$table->decimal('max_financial_regulatory_authority_rate',14,2)->default(0);
			$table->json('employee_profit_share_rates');
			$table->json('border_of_directors_profit_share_rates');
			$table->json('shareholders_first_dividend_portions');
			$table->json('shareholders_dividend_payout_ratios');
			$table->json('shareholders_dividend_in_cash_or_shares');
			$table->json('salaries_annual_increase_rates');
			$table->json('expense_annual_increase_rates');
			$table->json('cbe_lending_corridor_rates');
			$table->json('bank_lending_margin_rates');
			$table->json('credit_interest_rate_for_surplus_cash');
			
			$table->unsignedBigInteger('company_id');
		// ref	$table->foreign('company_id')->references('id')->on('veroanalysis_db.companies')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('general_and_reserve_assumptions');
    }
}
