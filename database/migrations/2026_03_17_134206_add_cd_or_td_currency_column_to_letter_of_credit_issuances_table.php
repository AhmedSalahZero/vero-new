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
        Schema::table('letter_of_credit_issuances', function (Blueprint $table) {
          $table->string('cd_or_td_currency')->nullable();
		  $table->string('lc_fees_and_commission_account_type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('letter_of_credit_issuances', function (Blueprint $table) {
            //
        });
    }
};
