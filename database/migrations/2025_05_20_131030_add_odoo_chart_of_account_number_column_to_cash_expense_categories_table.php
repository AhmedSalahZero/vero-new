<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOdooChartOfAccountNumberColumnToCashExpenseCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cash_expense_category_names', function (Blueprint $table) {
            $table->unsignedBigInteger('odoo_id')->after('id')->nullable();
			$table->string('odoo_chart_of_account_number')->after('odoo_id')->nullable();
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
