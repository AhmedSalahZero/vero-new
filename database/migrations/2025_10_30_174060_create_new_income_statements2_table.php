<?php

use App\Models\NonBankingService\Study;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNewIncomeStatements2Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       
		Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('cashflow_statement_reports', function (Blueprint $table) {
			foreach([
				// Study::LEASING,
				Study::DIRECT_FACTORING,
				// Study::REVERSE_FACTORING,
				// Study::IJARA,
				// Study::PORTFOLIO_MORTGAGE,
				// Study::MICROFINANCE
			] as $prefix){
				$table->json($prefix.'_bank_interest')->after($prefix.'_payment')->nullable();
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
