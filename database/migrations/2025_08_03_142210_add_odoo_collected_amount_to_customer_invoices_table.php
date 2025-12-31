<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOdooCollectedAmountToCustomerInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::table('customer_invoices', function (Blueprint $table) {
				$table->decimal('odoo_collected_amount',14,5)->after('invoice_status')->default(0);
				$table->decimal('odoo_collected_amount_in_main_currency',14,5)->after('odoo_collected_amount')->default(0);
				$table->decimal('total_collected_amount',14,5)->after('collected_amount_in_main_currency')->default(0);
				$table->decimal('total_collected_amount_in_main_currency',14,5)->after('total_collected_amount')->default(0);
      	  });
		  
		  Schema::table('supplier_invoices', function (Blueprint $table) {
				$table->decimal('odoo_paid_amount',14,5)->after('invoice_status')->default(0);
				$table->decimal('odoo_paid_amount_in_main_currency',14,5)->after('odoo_paid_amount')->default(0);
				$table->decimal('total_paid_amount',14,5)->after('paid_amount_in_main_currency')->default(0);
				$table->decimal('total_paid_amount_in_main_currency',14,5)->after('total_paid_amount')->default(0);
      	  });
		  
		  
        
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
