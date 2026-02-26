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
        Schema::create('expense_analysis', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('date');
            $table->unsignedBigInteger('company_id');
            $table->string('category_name')->nullable();
            $table->string('sub_category_name')->nullable();
            $table->string('expense_name')->nullable();
            $table->string('quantity_measurement_unit')->nullable();
            $table->string('quantity')->nullable();
            $table->string('cost_per_unit')->nullable();
            $table->string('total_cost')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_analysis');
    }
};
