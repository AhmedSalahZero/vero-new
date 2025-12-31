<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EditCreditColumnInCleanOverdraftBankStatements extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		foreach([
			'employee_statements','letter_of_credit_cash_cover_statements','lc_overdraft_bank_statements',
			'letter_of_credit_cash_cover_statements','letter_of_credit_statements','letter_of_guarantee_cash_cover_statements',
			'letter_of_guarantee_statements','loan_statements','overdraft_against_assignment_of_contract_bank_statements',
			'shareholder_statements',
			'subsidiary_company_statements',
			'cash_in_safe_statements','current_account_bank_statements','clean_overdraft_bank_statements','fully_secured_overdraft_bank_statements','lc_overdraft_bank_statements','overdraft_against_assignment_of_contract_bank_statements','overdraft_against_commercial_paper_bank_statements'] as $bankStatementTableName){
			
			Schema::table($bankStatementTableName, function (Blueprint $table) {
				$table->decimal('debit',14,2)->nullable()->default(0)->change();
				$table->decimal('credit',14,2)->nullable()->default(0)->change();
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
        Schema::table('clean_overdraft_bank_statements', function (Blueprint $table) {
            //
        });
    }
}
