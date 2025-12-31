<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOdooSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('odoo_settings', function (Blueprint $table) {
            $table->id();
			
			
			$table->string('cheques_receivable_code');
			$table->unsignedBigInteger('cheques_receivable_id');
			
			$table->string('suspense_account_code');
			$table->unsignedBigInteger('suspense_account_id');
			
			$table->string('cheques_payable_code');
			$table->unsignedBigInteger('cheques_payable_id');
			
			$table->string('bid_lg_cash_cover_code');
			$table->unsignedBigInteger('bid_lg_cash_cover_id');
			
			$table->string('final_lg_cash_cover_code');
			$table->unsignedBigInteger('final_lg_cash_cover_id');
			
			$table->string('advanced_lg_cash_cover_code');
			$table->unsignedBigInteger('advanced_lg_cash_cover_id');
			
			
			$table->string('performance_lg_cash_cover_code');
			$table->unsignedBigInteger('performance_lg_cash_cover_id');
			
			$table->string('sight_lc_cash_cover_code');
			$table->unsignedBigInteger('sight_lc_cash_cover_id');
			
			$table->string('deferred_lc_cash_cover_code');
			$table->unsignedBigInteger('deferred_lc_cash_cover_id');
			
			$table->unsignedBigInteger('company_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('odoo_settings');
    }
}
