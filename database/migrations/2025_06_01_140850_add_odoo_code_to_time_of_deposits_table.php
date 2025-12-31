<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOdooCodeToTimeOfDepositsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		foreach(['time_of_deposits','certificates_of_deposits'] as $tableName){
			 Schema::table($tableName, function (Blueprint $table) {
            $table->unsignedBigInteger('odoo_id')->after('id')->nullable();
			$table->string('odoo_code')->after('odoo_id')->nullable();
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
