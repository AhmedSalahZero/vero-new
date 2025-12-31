<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAnnualSalaryIncreaseRateToStudyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(FINANCIAL_PLANNING_CONNECTION_NAME)->table('studies', function (Blueprint $table) {
            $table->decimal('annual_salary_increase_rate',14,2)->after('corporate_taxes_rate')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('study', function (Blueprint $table) {
            //
        });
    }
}
