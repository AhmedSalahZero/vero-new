<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lc_against_td_or_cd_opening_balances', function (Blueprint $table) {
            $table->foreign(['financial_institution_id'], 'td_cc_fname')->references(['id'])->on('financial_institutions')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['lc_opening_balance_id'], 'td_opname_c')->references(['id'])->on('letter_of_credit_opening_balances')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lc_against_td_or_cd_opening_balances', function (Blueprint $table) {
            $table->dropForeign('td_cc_fname');
            $table->dropForeign('td_opname_c');
        });
    }
};
