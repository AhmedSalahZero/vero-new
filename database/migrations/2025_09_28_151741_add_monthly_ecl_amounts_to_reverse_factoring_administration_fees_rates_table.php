<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMonthlyEclAmountsToReverseFactoringAdministrationFeesRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		   Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('reverse_factoring_administration_fees_rates', function (Blueprint $table) {
            $table->json('monthly_ecl_values')->nullable();
            $table->json('accumulated_ecl_values')->nullable();
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
