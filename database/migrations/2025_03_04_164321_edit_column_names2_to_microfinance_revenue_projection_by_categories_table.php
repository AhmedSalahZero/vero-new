<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EditColumnNames2ToMicrofinanceRevenueProjectionByCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('microfinance_revenue_projection_by_categories', function (Blueprint $table) {
            $table->renameColumn('loan_case_amount','loan_case_amounts');
        });
		
		
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('microfinance_revenue_projection_by_categories', function (Blueprint $table) {
            //
        });
    }
}
