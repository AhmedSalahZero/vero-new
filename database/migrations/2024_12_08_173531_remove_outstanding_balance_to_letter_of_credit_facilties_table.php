<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveOutstandingBalanceToLetterOfCreditFaciltiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('letter_of_credit_facilities', function (Blueprint $table) {
            $table->dropColumn('outstanding_date');
            $table->dropColumn('outstanding_amount');
        });
		
		Schema::table('letter_of_credit_facility_term_and_conditions', function (Blueprint $table) {
            $table->dropColumn('outstanding_date');
            $table->dropColumn('outstanding_balance');
        });
		
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
       
    }
}
