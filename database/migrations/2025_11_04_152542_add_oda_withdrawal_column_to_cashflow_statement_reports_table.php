<?php

use App\Models\NonBankingService\Study;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOdaWithdrawalColumnToCashflowStatementReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('cashflow_statement_reports', function (Blueprint $table) {
            $table->json(Study::MICROFINANCE.'_oda_withdrawals')->after('withhold_payments')->nullable();
            $table->json(Study::CONSUMER_FINANCE.'_oda_withdrawals')->after('withhold_payments')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cashflow_statement_reports', function (Blueprint $table) {
            //
        });
    }
}
