<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInsuranceFromAccountCodeToOtherOdooSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('odoo_settings', function (Blueprint $table) {
            $table->string('insurance_from_account_code');
            $table->string('insurance_from_account_id');
			
			 $table->string('insurance_to_account_code');
            $table->string('insurance_to_account_id');
			
			
			
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
