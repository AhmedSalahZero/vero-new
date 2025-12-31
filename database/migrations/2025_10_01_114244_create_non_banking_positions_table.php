<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNonBankingPositionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('positions')->delete();
        DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('departments')->delete();
        Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->rename('positions', 'manpowers');
        Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('manpowers', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->dropColumn('department_id');
            $table->unsignedBigInteger('position_id')->after('id')->nullable();
        });
        Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->create('positions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('department_id');
            $table->unsignedBigInteger('company_id');
            $table->timestamps();
        });
		foreach([
			'direct_factoring_administration_fees_rates',
			'ijara_mortgage_administration_fees_rates',
			'microfinance_administration_fees_rates',
			'portfolio_mortgage_administration_fees_rates',
			'reverse_factoring_administration_fees_rates',
			'ijara_mortgage_new_portfolio_funding_structures',
			'reverse_factoring_new_portfolio_funding_structures',
			'microfinance_new_portfolio_funding_structures',
			'direct_factoring_new_portfolio_funding_structures',
			'portfolio_mortgage_new_portfolio_funding_structures'
		]as $tableName){
			Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->drop($tableName);
			
		}
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
