<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOpeningBalanceIdToCustomerInvoicesTable extends Migration
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
            $table->unsignedBigInteger('opening_balance_id')->after('company_id')->nullable();
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
