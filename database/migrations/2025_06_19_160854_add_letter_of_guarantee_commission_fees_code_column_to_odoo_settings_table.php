<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLetterOfGuaranteeCommissionFeesCodeColumnToOdooSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('odoo_settings', function (Blueprint $table) {
            foreach([
				'letter_of_guarantee_commission_fees_code',
				'letter_of_guarantee_issuance_fees_code',
				'letter_of_credit_commission_fees_code',
				'letter_of_credit_other_fees_code',
				'fully_secured_overdraft_interest_expense_code',
				'clean_overdraft_interest_expense_code',
				'overdraft_against_commercial_paper_interest_expense_code',
				'overdraft_against_contract_assignment_interest_expense_code',
				'medium_term_loan_interest_expense_code'
				] as $columnCodeName){
					$idColumnName = str_replace('_code','_id',$columnCodeName);
					$table->unsignedBigInteger($columnCodeName)->nullable();
					$table->unsignedBigInteger($idColumnName)->nullable();
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
        Schema::table('odoo_settings', function (Blueprint $table) {
            //
        });
    }
}
