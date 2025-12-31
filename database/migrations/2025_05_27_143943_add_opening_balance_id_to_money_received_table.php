<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOpeningBalanceIdToMoneyReceivedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		
		foreach(['money_received','money_payments'] as $tableName){
			 Schema::table($tableName, function (Blueprint $table) {
				$table->unsignedBigInteger('advanced_opening_balance_id')->nullable()->after('id');	
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
        Schema::table('money_received', function (Blueprint $table) {
            //
        });
    }
}
