<?php

use App\Models\NonBankingService\IncomeStatementReport;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAbcColumnToIncomeStatementReportsTable extends Migration
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
				'securitization_early_settlement_expense',
				'securitization_expense',
				'securitization_gain_or_loss',
				] as $columnName){
				$table->dropColumn($columnName);
			}
        });
		
		Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('income_statement_reports', function (Blueprint $table) {
			$table->json('securitization_early_settlement_expense')->after('securitization_collection_revenues')->nullable();
			$table->json('securitization_expense')->after('securitization_early_settlement_expense')->nullable();
			$table->json('securitization_gain_or_loss')->after('securitization_expense')->nullable();
        });
		
		
		Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('cashflow_statement_reports', function (Blueprint $table) {
			foreach([
				'securitization_npv',
				'securitization_bank_settlement',
				'securitization_early_settlement_expense',
				'securitization_expense',
				] as $columnName){
				$table->dropColumn($columnName);
			}
        });
		
		Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('cashflow_statement_reports', function (Blueprint $table) {
			$table->json('securitization_npv')->nullable()->after('securitization_reverse_loan_payment');
			$table->json('securitization_bank_settlement')->nullable()->after('securitization_npv');
			$table->json('securitization_early_settlement_expense')->nullable()->after('securitization_bank_settlement');
			$table->json('securitization_expense')->nullable()->after('securitization_early_settlement_expense');
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
