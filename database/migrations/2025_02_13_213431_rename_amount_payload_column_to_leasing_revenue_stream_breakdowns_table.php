<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameAmountPayloadColumnToLeasingRevenueStreamBreakdownsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		foreach(['direct_factoring_breakdowns','ijara_mortgage_breakdowns','reverse_factoring_breakdowns'] as $tableName){
			Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table($tableName, function (Blueprint $table) {
				$table->renameColumn('amount_payload','loan_amounts');
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
        Schema::table('leasing_revenue_stream_breakdowns', function (Blueprint $table) {
            //
        });
    }
}
