<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('quantity_sales_forecast', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('company_id')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('previous_year')->nullable();
            $table->string('previous_1_year_sales')->nullable();
            $table->decimal('previous_year_gr', 10, 4)->nullable();
            $table->string('average_last_3_years')->nullable();
            $table->string('others_products_previous_year')->nullable();
            $table->string('others_products_previous_3_year')->nullable();
            $table->longText('previous_year_seasonality')->nullable();
            $table->longText('last_3_years_seasonality')->nullable();
            $table->longText('forecasted_sales')->nullable();
            $table->enum('target_base', ['previous_year', 'previous_3_years', 'new_start'])->nullable();
            $table->decimal('prices_increase_rate', 10, 4)->nullable();
            $table->decimal('other_products_growth_rate', 10, 4)->nullable();
            $table->decimal('quantity_growth_rate', 10, 4)->nullable();
            $table->boolean('add_new_products')->default(false);
            $table->integer('number_of_products')->nullable();
            $table->enum('seasonality', ['previous_year', 'last_3_years', 'new_seasonality_monthly', 'new_seasonality_quarterly'])->nullable();
            $table->longText('new_seasonality')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quantity_sales_forecast');
    }
};
