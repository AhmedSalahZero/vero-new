<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOdooWithholdAmountToCustomerInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		foreach(['customer_invoices','supplier_invoices'] as $tableName){
			Schema::table($tableName, function (Blueprint $table) {
				$table->decimal('odoo_withhold_amount',14,5)->after('vat_amount_in_main_currency')->default(0);
				$table->decimal('odoo_withhold_amount_in_main_currency',14,5)->after('odoo_withhold_amount')->default(0);
				$table->decimal('total_withhold_amount',14,5)->after('withhold_amount_in_main_currency')->default(0);
				$table->decimal('total_withhold_amount_in_main_currency',14,5)->after('total_withhold_amount')->default(0);
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
