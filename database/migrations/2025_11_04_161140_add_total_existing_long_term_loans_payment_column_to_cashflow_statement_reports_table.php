<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTotalExistingLongTermLoansPaymentColumnToCashflowStatementReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('cashflow_statement_reports', function (Blueprint $table) {
			$columns = [
				'other_long_term_asset_collections',
				'existing_long_term_loans_payment',
				'existing_other_creditors_payment',
				'existing_other_debtors_collection',
				'existing_other_long_term_liabilities_payment',
				'expense_payments',
			];
			foreach($columns as $columnName){
				$table->json('total_'.$columnName)->after($columnName)->nullable();
			}
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
