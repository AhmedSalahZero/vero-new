<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTotalBranchesColumnToNewBranchMicrofinanceOpeningProjectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('new_branch_microfinance_opening_projections', function (Blueprint $table) {
            $table->unsignedInteger('total_branches')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('new_branch_microfinance_opening_projections', function (Blueprint $table) {
            //
        });
    }
}
