<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOutboundAccountBankStatementOdooIdColumnToTimeOfDepositsTable extends Migration
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
				$table->unsignedBigInteger('outbound_journal_entry_id')->after('id')->nullable();
				$table->unsignedBigInteger('inbound_journal_entry_id')->after('id')->nullable();
				$table->unsignedBigInteger('outbound_account_bank_statement_odoo_id')->after('id')->nullable();
				$table->unsignedBigInteger('inbound_account_bank_statement_odoo_id')->after('id')->nullable();
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
