<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOdooErrorMessageColumnToMoneyReceivedColumnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		foreach(['money_received','money_payments','cash_expenses','letter_of_guarantee_issuances'] as $tableName ){
			Schema::table($tableName, function (Blueprint $table) {
            $table->boolean('synced_with_odoo')->default(true)->after('id');
			$table->text('odoo_error_message')->after('id')->nullable();
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
        Schema::table('money_received_columns', function (Blueprint $table) {
            //
        });
    }
}
