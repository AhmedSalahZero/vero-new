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
        Schema::create('freelancer_expense_quick_pricing_calculator', function (Blueprint $table) {
            $table->string('freelancerExpenseAble_type');
            $table->unsignedBigInteger('freelancerExpenseAble_id');
            $table->unsignedBigInteger('freelancer_expense_id')->index('freelancer_p_id');
            $table->unsignedBigInteger('position_id')->index('pos_freelancer_id');
            $table->double('percentage_of_price')->default(0);
            $table->double('working_days')->default(0);
            $table->double('cost_per_day')->default(0);
            $table->double('total_cost')->default(0);
            $table->unsignedBigInteger('company_id')->index('company_id_freelancer_expense_quick_pricing_calculator');
            $table->unsignedBigInteger('creator_id')->nullable()->index('creator_id_freelancer_expense_quick_pricing_calculator');
            $table->timestamps();

            $table->index(['freelancerExpenseAble_type', 'freelancerExpenseAble_id'], 'freelancermorph');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('freelancer_expense_quick_pricing_calculator');
    }
};
