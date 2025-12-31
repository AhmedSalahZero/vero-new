<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTotalCasesCountColumnToMicrofinanceLoanOfficersCasesProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('microfinance_loan_officers_cases_projects', function (Blueprint $table) {
			$table->json('total_existing_officers_cases_count')->nullable();
			$table->json('total_new_officers_cases_count')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('microfinance_loan_officers_cases_projects', function (Blueprint $table) {
            //
        });
    }
}
