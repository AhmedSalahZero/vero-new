<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInterestRevenuesAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('interest_revenue_accounts', function (Blueprint $table) {
            $table->id();
			$table->unsignedBigInteger('financial_institution_id')->nullable()->comment('في حاله لو كانت null يبقي all');
			$table->string('odoo_code');
			$table->unsignedBigInteger('odoo_id');
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
        Schema::dropIfExists('interest_revenue_accounts');
    }
}
