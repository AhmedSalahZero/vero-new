<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOdooAccountBankStatementLineIdToCashExpensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cash_expenses', function (Blueprint $table) {
            $table->unsignedBigInteger('account_bank_statement_odoo_id')->after('id')->nullable();
            $table->unsignedBigInteger('journal_entry_id')->after('id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cash_expenses', function (Blueprint $table) {
            //
        });
    }
}
