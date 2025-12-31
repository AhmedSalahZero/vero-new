<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLcFacilityIdColumnToLcOverdraftWithdrawalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lc_overdraft_withdrawals', function (Blueprint $table) {
            $table->unsignedBigInteger('lc_facility_id')->nullable()->after('lc_overdraft_bank_statement_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lc_overdraft_withdrawals', function (Blueprint $table) {
            //
        });
    }
}
