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
        Schema::table('current_account_bank_statements', function (Blueprint $table) {
          $table->unsignedBigInteger('lg_commission_fees_journal_entry_id')->after('id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('current_account_bank_statements', function (Blueprint $table) {
            //
        });
    }
};
