<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFrequencyPerYearColumnToPortfolioMortgageRevenueProjectionByCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('portfolio_mortgage_revenue_projection_by_categories', function (Blueprint $table) {
			$table->json('frequency_per_year')->after('portfolio_mortgage_transactions_projections')->nullable();
			$table->json('start_from')->after('frequency_per_year')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('portfolio_mortgage_revenue_projection_by_categories', function (Blueprint $table) {
            //
        });
    }
}
