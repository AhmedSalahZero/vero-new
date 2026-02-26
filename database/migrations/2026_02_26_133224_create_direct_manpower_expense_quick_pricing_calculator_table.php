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
        Schema::create('direct_manpower_expense_quick_pricing_calculator', function (Blueprint $table) {
            $table->string('directManpowerExpenseAble_type');
            $table->unsignedBigInteger('directManpowerExpenseAble_id');
            $table->unsignedBigInteger('service_item_id')->nullable()->index('service_item_mex');
            $table->unsignedBigInteger('direct_manpower_expense_id')->index('dmp_id');
            $table->unsignedBigInteger('position_id')->index('pos_direct_id');
            $table->double('working_days')->default(0);
            $table->double('cost_per_day')->default(0);
            $table->double('total_cost')->default(0);
            $table->unsignedBigInteger('company_id')->index('company_id_direct_manpower_expense_quick_pricing_calculator');
            $table->unsignedBigInteger('creator_id')->nullable()->index('creator_id_direct_manpower_expense_quick_pricing_calculator');
            $table->timestamps();

            $table->index(['directManpowerExpenseAble_type', 'directManpowerExpenseAble_id'], 'directmanpowermorph');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('direct_manpower_expense_quick_pricing_calculator');
    }
};
