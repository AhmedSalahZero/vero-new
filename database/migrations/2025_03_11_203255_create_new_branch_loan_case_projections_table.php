<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNewBranchLoanCaseProjectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->create('new_branch_loan_case_projections', function (Blueprint $table) {
            $table->id();
			$table->integer('first_three_count');
			$table->integer('second_three_count');
			$table->integer('third_three_count');
			$table->integer('fourth_three_count');
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
        Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->dropIfExists('new_branch_loan_case_projections');
    }
}
