<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeSalariesAnnualIncreaseRatesColumnToGeneralAndReserveAssumptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('general_and_reserve_assumptions', function (Blueprint $table) {
			$table->json('salaries_annual_increase_rates')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('general_and_reserve_assumptions', function (Blueprint $table) {
            //
        });
    }
}
