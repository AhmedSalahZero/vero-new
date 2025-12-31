<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCashflowReportIdToWeeklyCashflowCustomDueInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		foreach(['weekly_cashflow_custom_due_invoices','weekly_cashflow_custom_past_due_schedules'] as $tableName){
			
			Schema::table($tableName, function (Blueprint $table) {
				$table->unsignedBigInteger('cashflow_report_id')->after('id')->default(0);
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
        Schema::table('weekly_cashflow_custom_due_invoices', function (Blueprint $table) {
            //
        });
    }
}
