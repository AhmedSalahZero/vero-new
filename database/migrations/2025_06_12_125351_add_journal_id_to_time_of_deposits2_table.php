<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddJournalIdToTimeOfDeposits2Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		foreach(['time_of_deposits','certificates_of_deposits'] as $tableName){
			Schema::table($tableName, function (Blueprint $table) {
				$table->unsignedBigInteger('store_journal_entry_id')->after('id')->nullable();
				$table->unsignedBigInteger('store_account_bank_statement_line_id')->after('id')->nullable();
				
				$table->unsignedBigInteger('maturity_journal_entry_id')->after('id')->nullable();
				$table->unsignedBigInteger('maturity_account_bank_statement_line_id')->after('id')->nullable();
				
				$table->unsignedBigInteger('interest_journal_entry_id')->after('id')->nullable();
				$table->unsignedBigInteger('interest_account_bank_statement_line_id')->after('id')->nullable();
				
				$table->unsignedBigInteger('renewal_journal_entry_id')->after('id')->nullable();
				$table->unsignedBigInteger('renewal_account_bank_statement_line_id')->after('id')->nullable();
				
				$table->unsignedBigInteger('break_journal_entry_id')->after('id')->nullable();
				$table->unsignedBigInteger('break_account_bank_statement_line_id')->after('id')->nullable();
				
				$table->unsignedBigInteger('outbound_break_account_bank_statement_odoo_id')->after('id')->nullable();
				$table->unsignedBigInteger('outbound_break_journal_entry_id')->after('id')->nullable();
				$table->unsignedBigInteger('inbound_break_account_bank_statement_odoo_id')->after('id')->nullable();
				$table->unsignedBigInteger('inbound_break_journal_entry_id')->after('id')->nullable();
				
				
				
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
        
    }
}
