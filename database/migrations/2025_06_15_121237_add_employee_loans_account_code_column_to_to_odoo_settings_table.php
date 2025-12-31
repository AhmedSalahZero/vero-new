<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEmployeeLoansAccountCodeColumnToToOdooSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('odoo_settings', function (Blueprint $table) {
            $table->string('employee_loans_account_code');
            $table->unsignedBigInteger('employee_loans_account_id');
			
			$table->string('dividend_payable_account_code');
            $table->unsignedBigInteger('dividend_payable_account_id');
			
			$table->string('sister_company_account_code');
            $table->unsignedBigInteger('sister_company_account_id');
			
			$table->string('shareholder_account_code');
            $table->unsignedBigInteger('shareholder_account_id');
			
			
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('to_odoo_settings', function (Blueprint $table) {
            //
        });
    }
}
