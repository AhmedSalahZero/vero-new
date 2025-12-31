<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTotalColumnToFinancialStatementAbleMainItemSubItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('financial_statement_able_main_item_sub_items', function (Blueprint $table) {
            $table->decimal('total',14,2)->default(0)->after('payload');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('financial_statement_able_main_item_sub_items', function (Blueprint $table) {
            //
        });
    }
}
