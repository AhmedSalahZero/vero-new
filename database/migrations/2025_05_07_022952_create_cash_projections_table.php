<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCashProjectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::dropIfExists('cash_projections');
        Schema::create('cash_projections', function (Blueprint $table) {
            $table->id();
			$table->string('name')->nullable();
			$table->string('type')->comment('in or out');
			$table->json('amounts')->nullable();
			$table->unsignedBigInteger('cashflow_report_id')->nullable();
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
        Schema::dropIfExists('cash_projections');
    }
}
