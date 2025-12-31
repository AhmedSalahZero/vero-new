<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOdooIdToMoneyReceivedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		foreach(['money_received','money_payments','cash_expenses'] as $tableName){
			Schema::table($tableName, function (Blueprint $table) {
				$table->unsignedBigInteger('odoo_id')->after('id')->nullable();
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
