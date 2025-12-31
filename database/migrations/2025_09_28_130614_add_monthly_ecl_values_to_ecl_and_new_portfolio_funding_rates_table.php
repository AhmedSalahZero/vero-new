<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMonthlyEclValuesToEclAndNewPortfolioFundingRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('ecl_and_new_portfolio_funding_rates', function (Blueprint $table) {
            $table->json('monthly_ecl_values')->nullable();
            $table->json('accumulated_ecl_values')->nullable();
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
