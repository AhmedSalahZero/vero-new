<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInboundBreakOdooReferenceColumnToTimeOfDepositsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		foreach([
			'time_of_deposits',
			'certificates_of_deposits'
		] as $columnName){
			Schema::table($columnName, function (Blueprint $table) {
				$table->string('inbound_break_odoo_reference')->after('id')->nullable();
				$table->string('store_break_journal_entry_id')->after('id')->nullable();
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
        Schema::table('time_of_deposits', function (Blueprint $table) {
            //
        });
    }
}
