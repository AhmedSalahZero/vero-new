<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSyncedWithOdooColumnToInternalMoneyTransferTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('internal_money_transfers', function (Blueprint $table) {
            $table->boolean('synced_with_odoo')->after('id')->nullable()->default(false);
            $table->string('odoo_error_message')->after('id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('internal_money_transfer', function (Blueprint $table) {
            //
        });
    }
}
