<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveTotalCasesCountColumnToMicrofinanceProductSalesProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('microfinance_loan_officers_cases_projects', function (Blueprint $table) {
        //     $table->dropColumn('total_cases_count');
        // });
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
