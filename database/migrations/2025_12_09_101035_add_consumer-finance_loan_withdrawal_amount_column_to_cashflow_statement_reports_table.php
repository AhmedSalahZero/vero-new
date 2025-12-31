<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddConsumerFinanceLoanWithdrawalAmountColumnToCashflowStatementReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $columnExist = Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->hasColumn('cashflow_statement_reports', 'consumer-finance_loan_withdrawal_amount');
        if (!$columnExist) {
            Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('cashflow_statement_reports', function (Blueprint $table) {
                $table->json('consumer-finance_loan_withdrawal_amount')->nullable()->after('portfolio-mortgage_loan_withdrawal_amount');
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
        Schema::table('cashflow_statement_reports', function (Blueprint $table) {
            //
        });
    }
}
