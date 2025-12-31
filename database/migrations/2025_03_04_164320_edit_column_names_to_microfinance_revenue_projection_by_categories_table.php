<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EditColumnNamesToMicrofinanceRevenueProjectionByCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('microfinance_revenue_projection_by_categories', function (Blueprint $table) {
            $table->renameColumn('microfinance_transactions_projections','loan_case_amount');
        });
		Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('microfinance_breakdowns', function (Blueprint $table) {
            $table->unsignedBigInteger('microfinance_product_id');
			$table->dropColumn('margin_rate');
			$table->dropColumn('sensitivity_margin_rate');
			$table->boolean('is_funding_by_mtl')->default(false);
			$table->renameColumn('contribution_percentage','contribution_percentages');
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
