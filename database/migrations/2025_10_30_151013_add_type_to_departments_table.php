<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeToDepartmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->drop('microfinance_positions');
		Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->drop('microfinance_departments');
		Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('positions', function (Blueprint $table) {
            $table->string('expense_type')->after('name')->nullable();
        });
		Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('departments', function (Blueprint $table) {
            $table->dropColumn('expense_type');
        });
		DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('positions')->delete();
		DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('departments')->delete();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('departments', function (Blueprint $table) {
            //
        });
    }
}
