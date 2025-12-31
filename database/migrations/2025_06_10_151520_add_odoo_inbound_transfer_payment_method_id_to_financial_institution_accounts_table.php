<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOdooInboundTransferPaymentMethodIdToFinancialInstitutionAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		foreach(['financial_institution_accounts','branch'] as $tableName){
			foreach([
				'odoo_inbound_transfer_payment_method_id',
				'odoo_outbound_transfer_payment_method_id',
				'odoo_inbound_cheque_payment_method_id',
				'odoo_outbound_cheque_payment_method_id'
				] as $columnName){
				Schema::table($tableName, function (Blueprint $table) use($columnName) {
						$table->string($columnName)->after('odoo_id')->nullable();
       			 });
			}
		}
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('financial_institution_accounts', function (Blueprint $table) {
            //
        });
    }
}
