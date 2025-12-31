<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEclAndNewPortfolioFundingRatesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('non_banking_service')->create('ecl_and_new_portfolio_funding_rates', function (Blueprint $table) {
            $table->id();
			$table->json('admin_fees_rates')->nullable();
			$table->json('ecl_rates')->nullable();
			$table->json('equity_funding_rates')->nullable();
			$table->json('equity_funding_values')->nullable();
			$table->json('new_loans_funding_rates')->nullable();
			$table->json('new_loans_funding_values')->nullable();
			$table->unsignedBigInteger('study_id');
			$table->unsignedBigInteger('company_id');
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
        Schema::dropIfExists('ecl_and_new_protfolio_tables');
    }
}
