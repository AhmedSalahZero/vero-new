<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTakafulCodeToOdooSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('odoo_settings', function (Blueprint $table) {
            foreach([
				'takaful_code',
				'tax_for_victims_code',
				] as $columnCodeName){
					$idColumnName = str_replace('_code','_id',$columnCodeName);
					$table->unsignedBigInteger($columnCodeName)->nullable();
					$table->unsignedBigInteger($idColumnName)->nullable();
				}
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
