<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInboundJournalEntryIdToColumnToBuyOrSellTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('buy_or_sell_currencies', function (Blueprint $table) {
			$table->string('odoo_error_message')->after('id')->nullable();
			$table->string('synced_with_odoo')->after('id')->nullable();
			$table->string('inbound_odoo_reference')->after('id')->nullable();
			$table->string('outbound_odoo_reference')->after('id')->nullable();
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
