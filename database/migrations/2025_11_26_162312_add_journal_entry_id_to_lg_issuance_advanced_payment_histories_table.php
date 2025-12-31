<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddJournalEntryIdToLgIssuanceAdvancedPaymentHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lg_issuance_advanced_payment_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('journal_entry_id')->nullable()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lg_issuance_advanced_payment_histories', function (Blueprint $table) {
            //
        });
    }
}
