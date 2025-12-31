<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFeesCodeColumnToOdooSettingsTable extends Migration
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
				'vat_taxes_code',
				'credit_withhold_taxes_code',
				'salary_taxes_code',
				'social_insurance_code',
				'income_taxes_code',
				'real_estate_taxes_code',
				'stamp_duty_taxes_code',
				'other_taxes_code',
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
        Schema::table('odoo_settings', function (Blueprint $table) {
            //
        });
    }
}
