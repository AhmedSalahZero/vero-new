<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMonthlyLoanAmountsColumnToMicrofinanceProductSalesProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('microfinance_product_sales_projects', function (Blueprint $table) {
            $table->json('monthly_loan_amounts')->after('monthly_product_mixes')->nullable();
            $table->json('total_cases_counts')->after('monthly_loan_amounts')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('microfinance_product_sales_projects', function (Blueprint $table) {
            //
        });
    }
}
