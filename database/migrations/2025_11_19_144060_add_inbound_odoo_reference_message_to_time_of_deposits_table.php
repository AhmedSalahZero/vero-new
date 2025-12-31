<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInboundOdooReferenceMessageToTimeOfDepositsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('time_of_deposits', function (Blueprint $table) {
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
        Schema::table('internal_money_transfers', function (Blueprint $table) {
            //
        });
    }
}
