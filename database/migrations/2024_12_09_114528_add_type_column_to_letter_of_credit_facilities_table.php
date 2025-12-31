<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeColumnToLetterOfCreditFacilitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('letter_of_credit_facilities', function (Blueprint $table) {
            $table->string('type')->after('id')->default('unsecured')->nullable()->comment('هل هو عادي ولا فولي سيكيورد');
			$table->string('cd_or_td_currency')->nullable()->after('currency');
			$table->unsignedBigInteger('cd_or_td_account_type_id')->nullable()->after('financing_duration');
			$table->unsignedBigInteger('cd_or_td_id')->nullable()->after('cd_or_td_account_type_id');
			$table->decimal('cd_or_td_amount',14,2)->nullable()->after('cd_or_td_id');
			$table->string('cd_or_td_interest')->nullable()->after('cd_or_td_amount');
			$table->string('cd_or_td_lending_percentage')->nullable()->after('cd_or_td_interest');
			
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('letter_of_credit_facilities', function (Blueprint $table) {
            //
        });
    }
}
