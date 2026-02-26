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
        Schema::create('quantity_existing_products_allocation_base', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('company_id')->nullable();
            $table->enum('allocation_base', ['branch', 'business_sector', 'sales_channel', 'zone']);
            $table->longText('existing_products_target')->nullable();
            $table->decimal('total_existing_target', 20)->nullable();
            $table->boolean('use_modified_targets')->default(false);
            $table->longText('allocation_base_percentages');
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
        Schema::dropIfExists('quantity_existing_products_allocation_base');
    }
};
