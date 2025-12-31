<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCommissionFeesJournalEntryIdColumnToLgRenewalFeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lg_renewal_date_histories', function (Blueprint $table) {
			 $table->unsignedBigInteger('renewal_fees_journal_entry_id')->nullable()->after('id');
            $table->string('renewal_fees_account_bank_statement_odoo_id')->nullable()->after('id');
			
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lg_renewal_date_histories', function (Blueprint $table) {
            //
        });
    }
}
