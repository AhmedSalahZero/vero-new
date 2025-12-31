<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIncreaseRatesColumnToMicrofinanceByBranchProductMixesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('microfinance_by_branch_product_mixes', function (Blueprint $table) {
			$table->json('increase_rates')->nullable();
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
