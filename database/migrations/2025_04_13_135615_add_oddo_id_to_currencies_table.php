<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOddoIdToCurrenciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('currencies', function (Blueprint $table) {
            $table->unsignedBigInteger('oddo_id')->after('id')->nullable();
        });
		foreach(['EGP'=>74,'EURO'=>125,'USD'=>1 ,'AED'=>128,'SAR'=>150,'QAR'=>155] as $currentName => $oddoId){
			DB::table('currencies')->where('name',$currentName)->update([
				'oddo_id'=>$oddoId
			]);
		}
		
		
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('currencies', function (Blueprint $table) {
            //
        });
    }
}
