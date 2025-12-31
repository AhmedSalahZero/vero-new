<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropLeasingMonthlyEclExpenseColumnToIncomeStatementReportsTable extends Migration
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
						'leasing_monthly_ecl_expense',
						'leasing_accumulated_ecl_expense',
						'direct-factoring_monthly_ecl_expense',
						'direct-factoring_accumulated_ecl_expense',
						'ijara_monthly_ecl_expense',
						'ijara_accumulated_ecl_expense',
						'reverse-factoring_monthly_ecl_expense',
						'reverse-factoring_accumulated_ecl_expense',
						'portfolio-mortgage_monthly_ecl_expense',
						'portfolio-mortgage_accumulated_ecl_expense',
						'microfinance_monthly_ecl_expense',
						'microfinance_accumulated_ecl_expense',
				
			] as $columnName){
				$table->dropColumn($columnName);
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
