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
        Schema::create('export_analysis', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('company_id');
            $table->string('revenue_stream')->nullable();
            $table->string('purchase_order_number')->nullable();
            $table->date('purchase_order_date')->nullable();
            $table->string('business_unit')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('consignee')->nullable();
            $table->string('loading_country')->nullable();
            $table->string('destination_country')->nullable();
            $table->string('broker')->nullable();
            $table->string('category')->nullable();
            $table->string('sub_category')->nullable();
            $table->string('product_item')->nullable();
            $table->string('origin')->nullable();
            $table->string('packing_unit_of_measurement')->nullable();
            $table->string('packing_quantity')->nullable();
            $table->string('packing_type')->nullable();
            $table->string('full_container_load_count')->nullable();
            $table->string('full_container_load_type')->nullable();
            $table->string('quantity_unit_of_measurement')->nullable();
            $table->string('quantity')->nullable();
            $table->string('incoterm')->nullable();
            $table->string('currency')->nullable();
            $table->string('price_per_unit')->nullable();
            $table->string('purchase_order_value')->nullable();
            $table->string('freight_value')->nullable();
            $table->string('purchase_order_net_value')->nullable();
            $table->string('payment_terms')->nullable();
            $table->string('shipping_line')->nullable();
            $table->string('booking_number')->nullable();
            $table->string('port_of_loading')->nullable();
            $table->string('port_of_destination')->nullable();
            $table->date('cut_off_date')->nullable();
            $table->date('estimated_time_of_sailing')->nullable();
            $table->date('estimated_time_of_arrival')->nullable();
            $table->string('inspection_company')->nullable();
            $table->string('clearance_agent')->nullable();
            $table->string('export_bank')->nullable();
            $table->string('documents_sending_type')->nullable();
            $table->string('purchase_order_status')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->integer('created_by')->nullable();
            $table->dateTime('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('export_analysis');
    }
};
