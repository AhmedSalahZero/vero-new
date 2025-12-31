<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSensitivityMarginRateColumnToReverseFactoringBreakdownsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('reverse_factoring_breakdowns', function (Blueprint $table) {
            $table->decimal('sensitivity_margin_rate',8,5)->after('margin_rate')->default(0);
        });
    }
	
    public function down()
    {
		
    }
}
