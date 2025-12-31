<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRevenueContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->create('revenue_contracts', function (Blueprint $table) {
            $table->id();
			$table->unsignedBigInteger('study_id');
			$table->unsignedBigInteger('company_id');
			// $table->string('type')->nullable();
			$table->string('category_id')->nullable(); // int or string 
			$table->json('monthly_loan_amounts')->nullable();
			$table->unsignedBigInteger('leasing_breakdown_id')->nullable();
			$table->unsignedBigInteger('ijara_breakdown_id')->nullable();
			$table->unsignedBigInteger('reverse_breakdown_id')->nullable();
			$table->unsignedBigInteger('direct_breakdown_id')->nullable();
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
        Schema::dropIfExists('revenue_contracts');
    }
}
