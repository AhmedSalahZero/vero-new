<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLoanCapitalizedInterestsColumnToFixedAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('fixed_assets', function (Blueprint $table) {
			$table->json('statement')->nullable();
			$table->json('ffe_equity_payment')->nullable();
            $table->json('ffe_loan_withdrawal')->nullable();
			$table->json('loan_capitalized_interests')->nullable();
            $table->json('income_statement_loan_capitalized_interests')->nullable();
            $table->json('ffe_loan_withdrawal_end_balance')->nullable();
			$table->json('depreciation_statement')->nullable();
            $table->json('capitalization_statement')->nullable();
			$table->json('ffe_execution_and_payment')->nullable();
			$table->json('ffe_payable')->nullable();
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
