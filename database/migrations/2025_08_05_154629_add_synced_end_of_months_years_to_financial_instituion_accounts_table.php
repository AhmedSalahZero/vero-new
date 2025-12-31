<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSyncedEndOfMonthsYearsToFinancialInstituionAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('financial_institution_accounts', function (Blueprint $table) {
            $table->json('synced_end_of_month_years')->nullable()->comment('لو عمل حركة مثلا في الفين خمسة وعشرين بنروح ننزل في السنه كاملة صفوف علشان ال
			end of month interest 
			ففي الكولوم دا هنسجل ان الفين خمسه وعشرين موجودة علشان ما نروحش ننزلهم تاني
			');
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
