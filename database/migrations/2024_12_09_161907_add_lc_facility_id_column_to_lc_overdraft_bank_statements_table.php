<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLcFacilityIdColumnToLcOverdraftBankStatementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lc_overdraft_bank_statements', function (Blueprint $table) {
            $table->unsignedBigInteger('lc_facility_id')->after('priority')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lc_overdraft_bank_statements', function (Blueprint $table) {
            //
        });
    }
}
