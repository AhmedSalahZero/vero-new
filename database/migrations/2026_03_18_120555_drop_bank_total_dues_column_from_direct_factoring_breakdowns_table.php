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
        Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('direct_factoring_breakdowns', function (Blueprint $table) {
			if (Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->hasColumn('direct_factoring_breakdowns', 'bank_total_dues')) {
				$table->dropColumn('bank_total_dues');
			}
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('direct_factoring_breakdowns', function (Blueprint $table) {
            //
        });
    }
};
