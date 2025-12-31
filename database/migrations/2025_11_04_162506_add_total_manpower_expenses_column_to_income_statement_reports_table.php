<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTotalManpowerExpensesColumnToIncomeStatementReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('income_statement_reports', function (Blueprint $table) {
            foreach([
				'manpower_expenses',
				'cost-of-service',
				'marketing-expense',
				'other-operation-expense',
				'sales-expense',
				'general-expense',
			] as $columnName){
				$table->json('total_'.$columnName)->after($columnName)->nullable();
			};
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('income_statement_reports', function (Blueprint $table) {
            //
        });
    }
}
