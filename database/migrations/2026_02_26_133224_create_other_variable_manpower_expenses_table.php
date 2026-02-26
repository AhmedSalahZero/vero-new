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
        Schema::create('other_variable_manpower_expenses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('expense_id')->nullable();
            $table->string('otherVariableManpowerExpenseAble_type');
            $table->unsignedBigInteger('otherVariableManpowerExpenseAble_id');
            $table->double('percentage_of_price')->default(0);
            $table->double('cost_per_unit')->default(0);
            $table->double('unit_cost')->default(0);
            $table->double('total_cost');
            $table->unsignedBigInteger('company_id')->index('company_id_other_variable_manpower_expenses');
            $table->unsignedBigInteger('creator_id')->nullable()->index('creator_id_other_variable_manpower_expenses');
            $table->timestamps();

            $table->index(['otherVariableManpowerExpenseAble_type', 'otherVariableManpowerExpenseAble_id'], 'othervariablemanpowerexpenseable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('other_variable_manpower_expenses');
    }
};
