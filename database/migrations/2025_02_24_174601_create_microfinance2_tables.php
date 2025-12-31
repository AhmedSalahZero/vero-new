<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMicrofinance2Tables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('microfinance_revenue_projection_by_categories', function (Blueprint $table) {
			$table->renameColumn('ijara_mortgage_transactions_projections','microfinance_transactions_projections');
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
