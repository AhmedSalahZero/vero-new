<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameSalesOrderIdToDownPaymentMoneyPaymentSettlementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('down_payment_money_payment_settlements', function (Blueprint $table) {
            $table->renameColumn('sales_order_id','purchase_order_id');
			$table->dropForeign('down_payment_money_payment_settlements_sales_order_id_foreign');
			$table->foreign('purchase_order_id')->references('id')->on('purchase_orders');
        });
		
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('down_payment_money_payment_settlements', function (Blueprint $table) {
            //
        });
    }
}
