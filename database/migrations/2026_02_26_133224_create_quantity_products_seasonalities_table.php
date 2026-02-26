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
        Schema::create('quantity_products_seasonalities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('company_id')->nullable();
            $table->string('name')->nullable();
            $table->integer('product_id')->nullable();
            $table->integer('category_id');
            $table->decimal('sales_target_value', 20, 4)->nullable();
            $table->decimal('sales_target_quantity', 20, 4)->nullable();
            $table->enum('seasonality', ['new_seasonality_monthly', 'new_seasonality_quarterly'])->nullable();
            $table->longText('seasonality_data')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quantity_products_seasonalities');
    }
};
