<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameOdooIdToCurrenciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		foreach(['currencies','customer_invoices','supplier_invoices','partners','contracts','sales_orders'] as $tableName){
			Schema::table($tableName, function (Blueprint $table) {
				$table->renameColumn('oddo_id','odoo_id');
			});
			
		}
		Schema::table('companies', function (Blueprint $table) {
				$table->renameColumn('oddo_db_url','odoo_db_url');
				$table->renameColumn('oddo_db_name','odoo_db_name');
				$table->renameColumn('oddo_username','odoo_username');
				$table->renameColumn('oddo_db_password','odoo_db_password');
			});
		
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('currencies', function (Blueprint $table) {
            //
        });
    }
}
