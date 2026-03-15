<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
		for($i = 1; $i <=8; $i++) {
			$sql = "ALTER TABLE non_banking_services.loan_schedule_payments DROP CONSTRAINT loan_schedule_payments_chk_$i;";
			DB::statement($sql);
			$sql = "ALTER TABLE non_banking_services.sensitivity_loan_schedule_payments DROP CONSTRAINT sensitivity_loan_schedule_payments_chk_$i;";
			DB::statement($sql);
		}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
