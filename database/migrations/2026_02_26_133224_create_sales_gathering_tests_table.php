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
        Schema::create('sales_gathering_tests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('company_id')->nullable();
            $table->date('date')->nullable();
            $table->string('country')->nullable();
            $table->string('local_or_export')->nullable();
            $table->string('branch')->nullable();
            $table->string('document_type')->nullable();
            $table->string('document_number')->nullable();
            $table->string('sales_person')->nullable();
            $table->string('customer_code')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('business_sector')->nullable();
            $table->string('zone')->nullable();
            $table->string('sales_channel')->nullable();
            $table->string('service_provider_type')->nullable();
            $table->string('service_provider_name')->nullable();
            $table->integer('service_provider_birth_year')->nullable();
            $table->string('principle')->nullable();
            $table->string('category')->nullable();
            $table->string('sub_category')->nullable();
            $table->string('product_or_service')->nullable();
            $table->string('product_item')->nullable();
            $table->string('measurment_unit')->nullable();
            $table->string('return_reason')->nullable();
            $table->decimal('quantity', 20, 4)->nullable();
            $table->string('quantity_status')->nullable();
            $table->decimal('quantity_bonus', 20, 4)->nullable();
            $table->decimal('price_per_unit', 20, 4)->nullable();
            $table->decimal('sales_value', 20, 4)->nullable();
            $table->decimal('quantity_discount', 20, 4)->nullable();
            $table->decimal('cash_discount', 20, 4)->nullable();
            $table->decimal('special_discount', 20, 4)->nullable();
            $table->decimal('other_discounts', 20, 4)->nullable();
            $table->decimal('net_sales_value', 20, 4)->nullable();
            $table->longText('validation')->nullable();
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
        Schema::dropIfExists('sales_gathering_tests');
    }
};
