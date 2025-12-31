<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOdooMoveIdColumnToMoneyReceivedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		foreach([
			'money_received',
			'settlements',
			'money_payments',
			'payment_settlements'
		] as $tableName){
			Schema::table($tableName, function (Blueprint $table) {
				$table->unsignedBigInteger('odoo_move_id')->nullable()->after('odoo_id');
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
