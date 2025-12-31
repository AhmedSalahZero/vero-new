<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserCommentsColumnToInternalMoneyTransfersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		foreach(['internal_money_transfers','lc_settlement_internal_money_transfers'] as $tableName){
			Schema::table($tableName, function (Blueprint $table) {
				$table->text('user_comment')->nullable();
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
		foreach(['internal_money_transfers','lc_settlement_internal_money_transfers'] as $tableName){
			Schema::table($tableName, function (Blueprint $table) {
				$table->dropColumn('user_comment');
			 });	
		}
        
    }
}
