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
        Schema::table('lc_hundred_percentage_cash_cover_opening_balances', function (Blueprint $table) {
            $table->foreign(['financial_institution_id'], 'lc__fname')->references(['id'])->on('financial_institutions')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['lc_opening_balance_id'], 'lc__opname')->references(['id'])->on('letter_of_credit_opening_balances')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lc_hundred_percentage_cash_cover_opening_balances', function (Blueprint $table) {
            $table->dropForeign('lc__fname');
            $table->dropForeign('lc__opname');
        });
    }
};
