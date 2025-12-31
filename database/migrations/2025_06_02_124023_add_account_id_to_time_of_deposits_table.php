<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAccountIdToTimeOfDepositsTable extends Migration
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
            // $table->unsignedBigInteger('deducted_from_account_type_id')->nullable()->after('id');
            $table->unsignedBigInteger('deducted_from_account_id')->nullable()->after('id');
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
