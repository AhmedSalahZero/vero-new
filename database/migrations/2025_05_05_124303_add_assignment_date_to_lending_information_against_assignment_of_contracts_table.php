<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAssignmentDateToLendingInformationAgainstAssignmentOfContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lending_information_against_assignment_of_contracts', function (Blueprint $table) {
            $table->date('assignment_date')->after('id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lending_information_against_assignment_of_contracts', function (Blueprint $table) {
            //
        });
    }
}
