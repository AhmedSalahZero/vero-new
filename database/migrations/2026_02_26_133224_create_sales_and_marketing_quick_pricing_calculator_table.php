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
        Schema::create('sales_and_marketing_quick_pricing_calculator', function (Blueprint $table) {
            $table->string('salesAndMarketingExpenseAble_type');
            $table->string('name')->nullable();
            $table->unsignedBigInteger('salesAndMarketingExpenseAble_id');
            $table->unsignedBigInteger('sales_and_marketing_expense_id')->index('sandm_p_id');
            $table->double('percentage_of_price')->default(0);
            $table->double('cost_per_unit')->default(0);
            $table->double('unit_cost')->default(0);
            $table->double('total_cost');
            $table->unsignedBigInteger('company_id')->index('company_id_sales_and_marketing_quick_pricing_calculator');
            $table->unsignedBigInteger('creator_id')->nullable()->index('creator_id_sales_and_marketing_quick_pricing_calculator');
            $table->timestamps();

            $table->index(['salesAndMarketingExpenseAble_type', 'salesAndMarketingExpenseAble_id'], 'salesandmarketingmorph');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_and_marketing_quick_pricing_calculator');
    }
};
