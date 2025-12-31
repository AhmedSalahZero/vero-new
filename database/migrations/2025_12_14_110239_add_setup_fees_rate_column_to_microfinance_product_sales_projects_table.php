<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSetupFeesRateColumnToMicrofinanceProductSalesProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('microfinance_product_sales_projects', function (Blueprint $table) {
            $table->json('fees_rates')->after('decrease_rates')->nullable();
            $table->json('setup_fees_durations')->after('decrease_rates')->nullable();
        });
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
