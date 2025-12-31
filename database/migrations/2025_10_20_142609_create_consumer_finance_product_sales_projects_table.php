<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConsumerfinanceProductSalesProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		
        Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->create('consumerfinance_product_sales_projects', function (Blueprint $table) {
            $table->id();
			$table->unsignedBigInteger('consumerfinance_product_id');
			$table->unsignedBigInteger('tenor')->default(0);
			$table->decimal('avg_amount',14,2)->default(0);
			$table->json('monthly_amounts')->nullable();
			$table->string('funded_by')->default(0)->comment('by-odas , by-mtls');
			$table->json('flat_rates')->nullable();
			$table->json('decrease_rates')->nullable();
			$table->json('loan_amounts')->nullable();
			$table->json('monthly_loan_amounts')->nullable();
			$table->studyFields();
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
        Schema::dropIfExists('microfinance_product_sales_projects');
    }
}
