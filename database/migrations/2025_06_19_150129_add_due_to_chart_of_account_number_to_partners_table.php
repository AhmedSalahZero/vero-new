<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDueToChartOfAccountNumberToPartnersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->integer('due_to_chart_of_account_number_odoo_code')->comment('خاصين بال subsidiary')->nullable();
            $table->integer('due_to_chart_of_account_number_odoo_id')->comment('خاصين بال subsidiary')->nullable();
			$table->integer('due_from_chart_of_account_number_odoo_code')->comment('خاصين بال subsidiary')->nullable();
            $table->integer('due_from_chart_of_account_number_odoo_id')->comment('خاصين بال subsidiary')->nullable();
			
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
