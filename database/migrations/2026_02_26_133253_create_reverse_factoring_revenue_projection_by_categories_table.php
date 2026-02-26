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
        Schema::connection('non_banking_service')->create('reverse_factoring_revenue_projection_by_categories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->longText('growth_rates')->nullable();
            $table->longText('reverse_factoring_transactions_projections')->nullable();
            $table->unsignedBigInteger('study_id');
            $table->unsignedBigInteger('company_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('non_banking_service')->dropIfExists('reverse_factoring_revenue_projection_by_categories');
    }
};
