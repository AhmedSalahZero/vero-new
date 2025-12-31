<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMonthlyLoanAmountsColumnToLeasingRevenueStreamBreakdownsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		foreach(['leasing_revenue_stream_breakdowns','direct_factoring_breakdowns','reverse_factoring_breakdowns','ijara_mortgage_breakdowns'] as $tableName){
			Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table($tableName, function (Blueprint $table) {
				$table->json('monthly_loan_amounts')->after('loan_amounts')->nullable();
			});
			
		}
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
