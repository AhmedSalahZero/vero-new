<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOccurrenceDatesColumnToPortfolioMortgageRevenueProjectionByCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('portfolio_mortgage_revenue_projection_by_categories', function (Blueprint $table) {
            $table->json('occurrence_dates')->nullable();
            $table->json('statement')->nullable();
            $table->json('loan_amounts')->nullable();
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
