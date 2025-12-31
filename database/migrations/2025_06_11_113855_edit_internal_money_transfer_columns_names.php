<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EditInternalMoneyTransferColumnsNames extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		foreach(['internal_money_transfers','buy_or_sell_currencies'] as $tableName){
			Schema::table($tableName,function(Blueprint $table){
				$table->renameColumn('odoo_outbound_payment_id','outbound_account_bank_statement_odoo_id');
				$table->renameColumn('odoo_inbound_payment_id','inbound_account_bank_statement_odoo_id');
				$table->unsignedBigInteger('outbound_journal_entry_id')->after('id')->nullable();
				$table->unsignedBigInteger('inbound_journal_entry_id')->after('id')->nullable();
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
        //
    }
}
