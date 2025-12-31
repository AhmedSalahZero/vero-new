<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPerpetualGrowthRateColumnToStudiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('studies', function (Blueprint $table) {
           	$table->decimal('cost_of_equity_rate',14,2)->default(0)->after('id');
           	$table->dropColumn('investment_return_rate');
           	$table->decimal('ebitda_multiplier',14,2)->default(0)->after('id');
           	$table->decimal('revenue_multiplier',14,2)->default(0)->after('id');
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
