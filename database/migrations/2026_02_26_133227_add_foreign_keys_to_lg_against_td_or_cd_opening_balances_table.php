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
        Schema::table('lg_against_td_or_cd_opening_balances', function (Blueprint $table) {
            $table->foreign(['financial_institution_id'], 'td_f_fname')->references(['id'])->on('financial_institutions')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['lg_opening_balance_id'], 'td_opname')->references(['id'])->on('letter_of_guarantee_opening_balances')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lg_against_td_or_cd_opening_balances', function (Blueprint $table) {
            $table->dropForeign('td_f_fname');
            $table->dropForeign('td_opname');
        });
    }
};
