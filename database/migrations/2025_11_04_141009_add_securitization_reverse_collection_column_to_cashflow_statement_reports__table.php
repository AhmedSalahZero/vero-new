<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSecuritizationReverseCollectionColumnToCashflowStatementReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('cashflow_statement_reports', function (Blueprint $table) {
			$table->json('securitization_reverse_collection')->after('ffe_loan_withdrawal')->nullable();
			$table->json('securitization_collection_revenues')->after('securitization_reverse_collection')->nullable();
			$table->json('securitization_reverse_loan_payment')->after('securitization_collection_revenues')->nullable();
			$table->decimal('securitization_npv',14,2)->after('securitization_reverse_loan_payment')->default(0);
			$table->decimal('securitization_bank_settlement',14,2)->after('securitization_npv')->default(0);
			$table->decimal('securitization_early_settlement_expense',14,2)->after('securitization_bank_settlement')->default(0);
			$table->decimal('securitization_expense',14,2)->after('securitization_early_settlement_expense')->default(0);
		});
		
		 Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('income_statement_reports', function (Blueprint $table) {
			$table->json('securitization_reverse_interest_revenues')->after('fixed_asset_loan_interest_expenses')->nullable();
			$table->json('securitization_reverse_loan_interest_expense')->after('securitization_reverse_interest_revenues')->nullable();
			$table->json('securitization_collection_revenues')->after('securitization_reverse_loan_interest_expense')->nullable();
			$table->decimal('securitization_early_settlement_expense',14,2)->after('securitization_collection_revenues')->default(0);
			$table->decimal('securitization_expense',14,2)->after('securitization_early_settlement_expense')->default(0);
			$table->decimal('securitization_gain_or_loss',14,2)->after('securitization_expense')->default(0);
		});
		
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cashflow_statement_reports_', function (Blueprint $table) {
            //
        });
    }
}
