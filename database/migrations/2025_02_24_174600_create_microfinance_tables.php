<?php

use Illuminate\Database\Migrations\Migration;

class CreateMicrofinanceTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		foreach([
			'ijara_mortgage_administration_fees_rates' => 'microfinance_administration_fees_rates',
			'ijara_mortgage_breakdowns'=>'microfinance_breakdowns',
			'ijara_mortgage_new_portfolio_funding_structures'=>'microfinance_new_portfolio_funding_structures',
			'ijara_mortgage_revenue_projection_by_categories'=>'microfinance_revenue_projection_by_categories'
		] as $oldTableName => $newTable){
			DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->statement('CREATE TABLE '.$newTable.' LIKE '.$oldTableName);
		}
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
