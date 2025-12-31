<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMicrofinanceBranchesToStudiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('studies', function (Blueprint $table) {
            $table->json('microfinance_branch_ids')->after('microfinance_loan_officer_count');
            $table->dropColumn('microfinance_branches_count');
        });
		
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('studies_', function (Blueprint $table) {
            //
        });
    }
}
