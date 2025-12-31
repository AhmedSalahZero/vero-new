<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMicrofinanceProductSalesProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		
		Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->drop('microfinance_revenue_projection_by_categories');
        Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->create('microfinance_product_sales_projects', function (Blueprint $table) {
            $table->id();
			$table->string('type')->comment('all-branches , new-branches,by-branch');
			$table->unsignedBigInteger('branch_id')->nullable();
			$table->unsignedBigInteger('microfinance_product_id');
			$table->unsignedBigInteger('tenor')->default(0);
			$table->decimal('avg_amount',14,2)->default(0);
			$table->string('funded_by')->default(0)->comment('by-odas , by-mtls');
			$table->json('product_mixes')->nullable();
			$table->json('monthly_product_mixes')->nullable();
			$table->json('seasonality')->nullable();
			$table->json('flat_rates')->nullable();
			$table->json('decrease_rates')->nullable();
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
