<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInboundOdooReferenceMessageToInternalMoneyTransfersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('internal_money_transfers', function (Blueprint $table) {
            $table->string('inbound_odoo_reference')->after('inbound_journal_entry_id')->nullable();
            $table->string('outbound_odoo_reference')->after('outbound_journal_entry_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('internal_money_transfers', function (Blueprint $table) {
            //
        });
    }
}
