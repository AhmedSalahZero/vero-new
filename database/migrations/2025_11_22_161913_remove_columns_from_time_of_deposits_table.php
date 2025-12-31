<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveColumnsFromTimeOfDepositsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		foreach([
			'time_of_deposits',
			'certificates_of_deposits'
		] as $tableName){
			Schema::table($tableName, function (Blueprint $table) use ($tableName) {
				foreach([
					'outbound_odoo_reference',
					'inbound_break_account_bank_statement_odoo_id',
					'outbound_break_account_bank_statement_odoo_id',
					'inbound_account_bank_statement_odoo_id',
					'outbound_account_bank_statement_odoo_id',
					'store_break_account_bank_statement_line_id'
				] as $dropColumnName){
					if(Schema::hasColumn($tableName,$dropColumnName)){
						$table->dropColumn($dropColumnName);
					}
				}
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
        Schema::table('time_of_deposits', function (Blueprint $table) {
            //
        });
    }
}
