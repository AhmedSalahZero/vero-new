<?php

use App\Models\NonBankingService\Study;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNewIncomeStatementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->create('income_statement_reports', function (Blueprint $table) {
            $table->id();
			foreach([
				Study::LEASING,
				Study::DIRECT_FACTORING,
				Study::REVERSE_FACTORING,
				Study::IJARA,
				Study::PORTFOLIO_MORTGAGE,
				Study::MICROFINANCE
			] as $prefix){
				$table->json($prefix.'_revenue')->nullable();
				$table->json($prefix.'_bank_interest')->nullable();
				$table->json($prefix.'_monthly_ecl_expense')->nullable();
				$table->json($prefix.'_accumulated_ecl_expense')->nullable();
			}
			$table->json('manpower_expenses')->nullable();
			$table->json('ecl_expenses')->nullable();
			$table->json('depreciation_expenses')->nullable();
			$table->json('oda_interests');
			foreach(['cost-of-service','marketing-expense','other-operation-expense','sales-expense','general-expense'] as $expenseType ){
				$table->json($expenseType)->nullable();
			}
			
			$table->json('corporate_taxes')->nullable();
			$table->unsignedBigInteger('study_id');
			$table->unsignedBigInteger('company_id');
            $table->timestamps();
        });
		Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->create('cashflow_statement_reports', function (Blueprint $table) {
            $table->id();
			foreach([
				Study::LEASING,
				Study::DIRECT_FACTORING,
				Study::REVERSE_FACTORING,
				Study::IJARA,
				Study::PORTFOLIO_MORTGAGE,
				Study::MICROFINANCE
			] as $prefix){
				$table->json($prefix.'_collection')->nullable();
				$table->json($prefix.'_loan_withdrawal_amount')->nullable();
				$table->json($prefix.'_payment')->nullable();
			}
			// $table->json('oda_interests');
			// foreach(['cost-of-service','marketing-expense','other-operation-expense','sales-expense','general-expense'] as $expenseType ){
			// 	$table->json($expenseType)->nullable();
			// }
			
			// $table->json('corporate_taxes')->nullable();
			$table->unsignedBigInteger('study_id');
			$table->unsignedBigInteger('company_id');
            $table->timestamps();
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
