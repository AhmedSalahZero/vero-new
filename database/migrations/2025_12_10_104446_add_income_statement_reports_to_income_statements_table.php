<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIncomeStatementReportsToIncomeStatementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $columnExist = Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->hasColumn('income_statement_reports', 'consumer-finance_revenue');
        if (!$columnExist) {
            Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('income_statement_reports', function (Blueprint $table) {
                $table->json('consumer-finance_revenue')->after('microfinance_revenue')->nullable();
                $table->json('consumer-finance_bank_interest')->after('microfinance_bank_interest')->nullable();
            });
        
            Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('cashflow_statement_reports', function (Blueprint $table) {
                $table->json('consumer-finance_collection')->after('microfinance_collection')->nullable();
                $table->json('consumer-finance_payment')->after('microfinance_payment')->nullable();
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
        Schema::table('income_statements', function (Blueprint $table) {
            //
        });
    }
}
