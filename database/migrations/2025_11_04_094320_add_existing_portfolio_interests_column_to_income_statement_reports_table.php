<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExistingPortfolioInterestsColumnToIncomeStatementReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('income_statement_reports', function (Blueprint $table) {
			$table->json('existing_interests_revenues')->nullable()->after('id');
            $table->json('existing_interests_expense')->nullable()->after('existing_interests_revenues');
            $table->json('existing_loans_interests_expense')->nullable()->after('existing_interests_expense');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('income_statement_reports', function (Blueprint $table) {
            //
        });
    }
}
