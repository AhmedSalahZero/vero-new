<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOdooReferenceColumnToInternalMoneyTransfersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('internal_money_transfers', function (Blueprint $table) {
            // $table->boolean('synced_with_odoo')->after('id')->nullable();
            // $table->string('odoo_error_message')->after('id')->nullable();
            // $table->string('inbound_odoo_reference')->after('id')->nullable();
            // $table->string('outbound_odoo_reference')->after('id')->nullable();
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
