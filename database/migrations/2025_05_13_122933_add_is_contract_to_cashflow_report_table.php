<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsContractToCashflowReportTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		foreach([
			 'weekly_cashflow_custom_due_invoices','weekly_cashflow_custom_past_due_schedules','cashflow_reports',
		'cash_projections'] as $tableName){
			Schema::table($tableName, function (Blueprint $table) {
				$table->boolean('is_contract')->comment('is contract cash flow (1) or is cash flow (0)')->default(0)->after('id');
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
