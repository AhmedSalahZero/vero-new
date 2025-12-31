<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEclExistingRateToCashAndBankOpeningBalancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('cash_and_bank_opening_balances', function (Blueprint $table) {
			$table->decimal('ecl_existing_rate',14,2)->after('id')->default(0);
			$table->json('ecl_existing_expenses')->after('expected_credit_loss')->nullable();
			$table->json('accumulated_ecl_existing_expenses')->after('ecl_existing_expenses')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fixed_asset_opening_balances', function (Blueprint $table) {
            //
        });
    }
}
