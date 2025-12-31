<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAccuredInterestColumnToLoanSchedulePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		foreach([
			'loan_schedule_payments',
			'sensitivity_loan_schedule_payments'
		] as $tableName){
			Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table($tableName, function (Blueprint $table) {
				$table->json('accured_interest')->after('schedulePayment')->nullable();
				$table->json('no_securitization')->after('endBalance')->nullable();
				
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
        Schema::table('loan_schedule_payments', function (Blueprint $table) {
            //
        });
    }
}
