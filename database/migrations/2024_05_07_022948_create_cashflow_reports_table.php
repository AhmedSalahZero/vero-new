<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCashflowReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::dropIfExists('cashflow_reports');
        Schema::create('cashflow_reports', function (Blueprint $table) {
            $table->id();
			$table->string('report_name')->nullable();
			$table->string('report_interval')->comment('monthly,  weekly ..etc');
			$table->string('start_date');
			$table->string('end_date');
			$table->json('report_data');
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
        Schema::dropIfExists('cashflow_reports');
    }
}
