<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsAtMaturityToTimeOfDepositsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		foreach(['time_of_deposits' , 'certificates_of_deposits'] as $tableName){
			Schema::table($tableName, function (Blueprint $table) {
				// $table->boolean('is_periodically')->after('id')->default(false)  ;
				$table->boolean('is_at_maturity')->after('id')->default(true)  ;
			});
			
		}
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('time_of_deposits', function (Blueprint $table) {
            //
        });
    }
}
