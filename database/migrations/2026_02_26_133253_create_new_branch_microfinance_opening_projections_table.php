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
        Schema::connection('non_banking_service')->create('new_branch_microfinance_opening_projections', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('counts');
            $table->integer('start_date');
            $table->unsignedBigInteger('study_id');
            $table->unsignedBigInteger('company_id');
            $table->timestamps();
            $table->unsignedInteger('operation_date');
            $table->unsignedInteger('total_branches')->default(0);
            $table->json('rent_payment_amount')->nullable();
            $table->json('right_of_use_interest')->nullable();
            $table->json('rent_liability_statement')->nullable();
            $table->json('right_of_use_statement')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('non_banking_service')->dropIfExists('new_branch_microfinance_opening_projections');
    }
};
