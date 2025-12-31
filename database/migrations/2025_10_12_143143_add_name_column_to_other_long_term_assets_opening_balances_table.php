<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNameColumnToOtherLongTermAssetsOpeningBalancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('other_long_term_assets_opening_balances', function (Blueprint $table) {
            $table->string('name')->after('id')->nullable();
        });
		
		Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('long_term_loan_opening_balances', function (Blueprint $table) {
            $table->string('name')->after('id')->nullable();
        });
		
		Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('other_long_term_liabilities_opening_balances', function (Blueprint $table) {
            $table->string('name')->after('id')->nullable();
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
