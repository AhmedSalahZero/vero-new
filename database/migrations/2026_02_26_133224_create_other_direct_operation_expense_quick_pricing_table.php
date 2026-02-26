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
        Schema::create('other_direct_operation_expense_quick_pricing', function (Blueprint $table) {
            $table->string('OtherDirectOperationExpenseAble_type');
            $table->string('name')->nullable();
            $table->unsignedBigInteger('OtherDirectOperationExpenseAble_id');
            $table->unsignedBigInteger('other_direct_operation_expense_id')->index('odop_id');
            $table->double('percentage_of_price')->default(0);
            $table->double('cost_per_unit')->default(0);
            $table->double('unit_cost')->default(0);
            $table->double('total_cost');
            $table->unsignedBigInteger('company_id')->index('company_id_other_direct_operation_expense_quick_pricing');
            $table->unsignedBigInteger('creator_id')->nullable()->index('creator_id_other_direct_operation_expense_quick_pricing');
            $table->timestamps();

            $table->index(['OtherDirectOperationExpenseAble_type', 'OtherDirectOperationExpenseAble_id'], 'odoeable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('other_direct_operation_expense_quick_pricing');
    }
};
