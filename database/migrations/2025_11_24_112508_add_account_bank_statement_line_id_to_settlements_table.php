<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAccountBankStatementLineIdToSettlementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		foreach([
			'payment_settlements',
			'settlements'
		] as $columnName){
			Schema::table($columnName, function (Blueprint $table) {
            $table->unsignedBigInteger('account_bank_statement_line_id')->nullable()->after('odoo_id');
            $table->string('odoo_reference')->nullable()->after('odoo_id');
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
        Schema::table('settlements', function (Blueprint $table) {
            //
        });
    }
}
