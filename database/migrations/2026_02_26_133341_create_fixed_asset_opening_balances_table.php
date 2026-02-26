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
        Schema::connection('property_management')->create('fixed_asset_opening_balances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('name_id')->nullable();
            $table->decimal('gross_amount', 14);
            $table->decimal('accumulated_depreciation', 14)->default(0);
            $table->integer('monthly_counts')->default(0);
            $table->decimal('admin_depreciation_percentage', 14)->default(0);
            $table->decimal('manufacturing_depreciation_percentage', 14)->default(0);
            $table->longText('product_allocations')->nullable();
            $table->longText('monthly_product_allocations')->nullable();
            $table->unsignedBigInteger('is_as_revenue_percentages')->default(1);
            $table->unsignedBigInteger('study_id');
            $table->timestamps();
            $table->decimal('monthly_depreciation')->default(0);
            $table->longText('admin_depreciations')->nullable();
            $table->longText('statement')->nullable();
            $table->longText('manufacturing_depreciations')->nullable();
            $table->longText('monthly_accumulated_depreciations')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('property_management')->dropIfExists('fixed_asset_opening_balances');
    }
};
