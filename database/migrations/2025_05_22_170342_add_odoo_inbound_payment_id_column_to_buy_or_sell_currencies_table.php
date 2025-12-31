<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOdooInboundPaymentIdColumnToBuyOrSellCurrenciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('buy_or_sell_currencies', function (Blueprint $table) {
            $table->unsignedBigInteger('odoo_inbound_payment_id')->nullable()->after('id');
            $table->unsignedBigInteger('odoo_outbound_payment_id')->nullable()->after('id');
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
