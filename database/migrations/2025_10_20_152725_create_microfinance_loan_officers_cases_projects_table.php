<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMicrofinanceLoanOfficersCasesProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->create('microfinance_loan_officers_cases_projects', function (Blueprint $table) {
            $table->id();
			$table->string('type')->comment('all-branches , new-branches,by-branch');
			$table->unsignedBigInteger('branch_id')->nullable();
			$table->boolean('is_senior')->default(0);
			
			$table->json('existing_cases')->nullable();
			$table->json('hiring')->nullable();
			$table->unsignedBigInteger('existing_count')->nullable();
			$table->json('new_cases')->nullable();
			
			$table->studyFields();
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
        Schema::dropIfExists('microfinance_loan_officers_cases_projects');
    }
}
