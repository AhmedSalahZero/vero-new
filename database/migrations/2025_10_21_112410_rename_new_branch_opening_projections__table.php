<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameNewBranchOpeningProjectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->rename('new_branch_opening_projections','new_branch_microfinance_opening_projections');
		Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('new_branch_microfinance_opening_projections',function(Blueprint $table){
			$table->renameColumn('start_date_as_index','start_date');
			$table->unsignedInteger('operation_date');
			$table->dropColumn('loan_officer_count_per_branch');
			$table->dropColumn('total_branches');
			// id	counts	start_date_as_index	loan_officer_count_per_branch	total_branches	study_id	company_id	created_at	updated_at
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
