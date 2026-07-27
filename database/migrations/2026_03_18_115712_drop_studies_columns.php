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
        Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('studies', function (Blueprint $table) {
			if (Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->hasColumn('studies', 'microfinance_loan_officer_count')) {
				$table->dropColumn('microfinance_loan_officer_count');
			}
			
			if (Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->hasColumn('studies', 'consumerfinance_branches_count')) {
				$table->dropColumn('consumerfinance_branches_count');
			}
			if (Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->hasColumn('studies', 'consumerfinance_loan_officer_count')) {
				$table->dropColumn('consumerfinance_loan_officer_count');
			}
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
