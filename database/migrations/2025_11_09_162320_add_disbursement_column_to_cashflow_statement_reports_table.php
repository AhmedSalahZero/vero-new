<?php

use App\Models\NonBankingService\Study;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDisbursementColumnToCashflowStatementReportsTable extends Migration
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
				  Study::LEASING=>__('Leasing'),
				 Study::DIRECT_FACTORING=>__('Direct Factoring'),
				 Study::IJARA=>__('Ijara'),
				 Study::REVERSE_FACTORING=>__('Reverse Factoring'),
				 Study::PORTFOLIO_MORTGAGE=>__('Portfolio Mortgage'),
				 Study::MICROFINANCE => __('Microfinance'),
			] as $prefix => $x){
				$table->json($prefix .'_disbursements')->nullable()->after('id');
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
