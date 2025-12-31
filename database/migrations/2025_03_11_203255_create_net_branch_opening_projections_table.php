<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNetBranchOpeningProjectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->create('new_branch_opening_projections', function (Blueprint $table) {
            $table->id();
			$table->integer('counts');
			$table->integer('start_date_as_index');
			$table->integer('loan_officer_count_per_branch');
			$table->integer('total_branches');
			$table->unsignedBigInteger('study_id');
			$table->unsignedBigInteger('company_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->dropIfExists('new_branch_opening_projections');
    }
}
