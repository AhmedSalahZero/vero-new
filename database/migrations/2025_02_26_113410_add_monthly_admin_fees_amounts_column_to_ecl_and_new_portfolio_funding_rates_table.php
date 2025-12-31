<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMonthlyAdminFeesAmountsColumnToEclAndNewPortfolioFundingRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		foreach([
			'ijara_mortgage_administration_fees_rates','direct_factoring_administration_fees_rates','ecl_and_new_portfolio_funding_rates','portfolio_mortgage_administration_fees_rates',
			'reverse_factoring_administration_fees_rates'] as $tableName){
			Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table($tableName, function (Blueprint $table) {
				$table->json('monthly_admin_fees_amounts')->nullable()->after('admin_fees_rates');
			});
		}
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ecl_and_new_portfolio_funding_rates', function (Blueprint $table) {
            //
        });
    }
}
