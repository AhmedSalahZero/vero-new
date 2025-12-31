<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCommissionFeesJournalEntryIdColumnToLetterOfGuaranteeIssuancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('letter_of_guarantee_issuances', function (Blueprint $table) {
            $table->unsignedBigInteger('commission_fees_journal_entry_id')->nullable()->after('id');
            $table->string('commission_fees_account_bank_statement_odoo_id')->nullable()->after('id');
			 $table->unsignedBigInteger('issuance_fees_journal_entry_id')->nullable()->after('id');
            $table->string('issuance_fees_account_bank_statement_odoo_id')->nullable()->after('id');
			//  $table->unsignedBigInteger('renewal_fees_journal_entry_id')->after('id');
            // $table->string('renewal_fees_account_bank_statement_odoo_id')->after('id');
			
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('letter_of_guarantee_issuances', function (Blueprint $table) {
            //
        });
    }
}
