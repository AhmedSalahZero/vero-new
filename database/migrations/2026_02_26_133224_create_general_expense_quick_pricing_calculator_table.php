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
        Schema::create('general_expense_quick_pricing_calculator', function (Blueprint $table) {
            $table->string('generalExpenseAble_type');
            $table->unsignedBigInteger('generalExpenseAble_id');
            $table->unsignedBigInteger('general_expense_id')->index('gndm_p_id');
            $table->double('percentage_of_price')->default(0);
            $table->string('name')->nullable();
            $table->double('cost_per_unit')->default(0);
            $table->double('unit_cost')->default(0);
            $table->double('total_cost');
            $table->unsignedBigInteger('company_id')->index('company_id_general_expense_quick_pricing_calculator');
            $table->unsignedBigInteger('creator_id')->nullable()->index('creator_id_general_expense_quick_pricing_calculator');
            $table->timestamps();

            $table->index(['generalExpenseAble_type', 'generalExpenseAble_id'], 'generalmorph');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('general_expense_quick_pricing_calculator');
    }
};
