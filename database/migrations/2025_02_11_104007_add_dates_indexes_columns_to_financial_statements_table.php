<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDatesIndexesColumnsToFinancialStatementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('financial_statements', function (Blueprint $table) {
			$table->date('study_start_date')->nullable();
			$table->integer('duration_in_years')->default(0);
			$table->date('study_end_date')->nullable();
            $table->json('study_dates')->nullable();
            $table->json('operation_dates')->nullable();
			$table->double('operation_start_month',8,2)->nullable();
			$table->date('operation_start_date')->nullable();
			$table->string('financial_year_start_month')->default('january');
			
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('financial_statements', function (Blueprint $table) {
            //
        });
    }
}
