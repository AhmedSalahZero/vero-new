<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRevenueStreamTypeToEclAndNewPortfolioFundingRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('non_banking_service')->table('ecl_and_new_portfolio_funding_rates', function (Blueprint $table) {
            $table->string('revenue_stream_type')->comment('leasing,factoring,...etc')->after('id');
        });
		
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
