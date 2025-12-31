<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGracePeriodColumnToIjaraMortgageBreakdownsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('non_banking_service')->table('ijara_mortgage_breakdowns', function (Blueprint $table) {
            $table->unsignedBigInteger('grace_period')->after('margin_rate')->default(0)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ijara_mortgage_breakdowns', function (Blueprint $table) {
            //
        });
    }
}
