<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExpenseAsPercentagesColumnToExpensesTable extends Migration
{

    public function up()
    {
        Schema::connection('non_banking_service')->table('expenses', function (Blueprint $table) {
            $table->json('expense_as_percentages')->after('monthly_repeating_amounts')->nullable();
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
