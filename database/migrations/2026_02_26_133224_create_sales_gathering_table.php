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
        Schema::create('sales_gathering', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->integer('company_id')->nullable();
            $table->date('date')->nullable();
            $table->string('day_name', 10)->nullable();
            $table->string('country', 20)->nullable();
            $table->string('local_or_export', 50)->nullable();
            $table->string('branch', 100)->nullable();
            $table->string('document_type', 50)->nullable();
            $table->string('document_number', 50)->nullable();
            $table->string('sales_person', 150)->nullable();
            $table->string('business_unit', 150)->nullable();
            $table->string('customer_name', 150)->nullable();
            $table->string('business_sector', 150)->nullable();
            $table->string('zone', 100)->nullable();
            $table->string('sales_channel', 100)->nullable();
            $table->string('service_provider_type', 50)->nullable();
            $table->string('service_provider_name', 50)->nullable();
            $table->integer('service_provider_birth_year')->nullable();
            $table->string('principle', 100)->nullable();
            $table->string('category', 50)->nullable();
            $table->string('sub_category', 50)->nullable();
            $table->string('product_or_service', 150)->nullable();
            $table->string('product_item', 150)->nullable();
            $table->string('measurment_unit', 15)->nullable();
            $table->string('return_reason', 50)->nullable();
            $table->decimal('quantity', 20, 4)->nullable();
            $table->string('quantity_status', 50)->nullable();
            $table->decimal('quantity_bonus', 20, 4)->nullable();
            $table->decimal('price_per_unit', 20, 4)->nullable();
            $table->decimal('sales_value', 20, 4)->nullable();
            $table->decimal('quantity_discount', 20, 4)->nullable();
            $table->decimal('cash_discount', 20, 4)->nullable();
            $table->decimal('special_discount', 20, 4)->nullable();
            $table->decimal('other_discounts', 20, 4)->nullable();
            $table->decimal('net_sales_value', 20, 4)->nullable();
            $table->string('updated_by', 50)->nullable();
            $table->string('created_by', 50)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->string('Day', 10)->nullable()->virtualAs('lpad(dayofmonth(`date`),2,_utf8mb4\'0\')');
            $table->string('Month', 10)->nullable()->virtualAs('lpad(month(`date`),2,_utf8mb4\'0\')');
            $table->string('Year', 10)->nullable()->virtualAs('year(`date`)');

            $table->index(['net_sales_value', 'date', 'customer_name', 'company_id'], 'commin_index');
            $table->index(['company_id', 'date', 'Year', 'Month', 'net_sales_value'], 'interval__index');
            $table->index(['branch', 'company_id'], 'ix__branch_index');
            $table->index(['business_sector', 'company_id'], 'ix__business_sector_index');
            $table->index(['category', 'company_id'], 'ix__category_index');
            $table->index(['product_item', 'company_id'], 'ix__product_item_index');
            $table->index(['product_or_service', 'company_id'], 'ix__product_or_service_index');
            $table->index(['sales_channel', 'company_id'], 'ix__sales_channel_index');
            $table->index(['sales_person', 'company_id'], 'ix__sales_person_index');
            $table->index(['customer_name', 'Year', 'Month', 'company_id', 'net_sales_value'], 'min__index');
            $table->index(['company_id', 'customer_name', 'Month', 'branch', 'net_sales_value', 'Year'], 'min__index_branch');
            $table->index(['company_id', 'customer_name', 'Month', 'business_sector', 'net_sales_value', 'Year'], 'min__index_business_sector');
            $table->index(['company_id', 'customer_name', 'Month', 'category', 'net_sales_value', 'Year'], 'min__index_category');
            $table->index(['company_id', 'customer_name', 'Month', 'country', 'net_sales_value', 'Year'], 'min__index_country');
            $table->index(['company_id', 'customer_name', 'Month', 'product_item', 'net_sales_value', 'Year'], 'min__index_product_item');
            $table->index(['company_id', 'customer_name', 'Month', 'product_or_service', 'net_sales_value', 'Year'], 'min__index_product_or_service');
            $table->index(['company_id', 'customer_name', 'Month', 'sales_channel', 'net_sales_value', 'Year'], 'min__index_sales_channel');
            $table->index(['company_id', 'customer_name', 'Month', 'sales_person', 'net_sales_value', 'Year'], 'min__index_sales_person');
            $table->index(['company_id', 'customer_name', 'Month', 'zone', 'net_sales_value', 'Year'], 'min__index_zone');
            $table->index(['company_id', 'sales_channel', 'date', 'net_sales_value', 'service_provider_name', 'id'], 'sales_channel_index');
            $table->index(['date', 'sales_person', 'product_item', 'product_or_service', 'customer_name'], 'sales_gathering_customer_index');
            $table->index(['zone', 'sales_channel'], 'sales_gathering_zone_sales');
            $table->index(['Year', 'Month', 'Day'], 'year_month_year');
            $table->index(['zone', 'company_id'], 'zones_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_gathering');
    }
};
