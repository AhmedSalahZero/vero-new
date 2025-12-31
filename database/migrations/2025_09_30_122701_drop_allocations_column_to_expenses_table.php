<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropAllocationsColumnToExpensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('expenses', function (Blueprint $table) {
            $table->dropColumn('allocation_base_1');
            $table->dropColumn('allocation_base_2');
            $table->dropColumn('allocation_base_3');
            $table->dropColumn('conditional_to');
            $table->dropColumn('conditional_value_a');
            $table->dropColumn('conditional_value_b');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('expenses', function (Blueprint $table) {
            //
        });
    }
}
