<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EditRevenueStreamTypeCategoryToExpensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('non_banking_service')->table('expenses', function (Blueprint $table) {
			$table->json('revenue_stream_type')->nullable()->change();
			$table->json('stream_category_ids')->after('revenue_stream_type')->nullable();
			$table->renameColumn('month_percentage','monthly_percentage')->nullable()->change();
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
