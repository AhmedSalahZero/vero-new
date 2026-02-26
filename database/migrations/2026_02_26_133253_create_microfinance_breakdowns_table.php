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
        Schema::connection('non_banking_service')->create('microfinance_breakdowns', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('installment_interval');
            $table->unsignedBigInteger('grace_period')->nullable()->default(0);
            $table->double('tenor')->default(0);
            $table->longText('decreasing_rates')->nullable();
            $table->longText('flat_rates')->nullable();
            $table->longText('contribution_percentages')->nullable();
            $table->longText('loan_amounts')->nullable();
            $table->unsignedBigInteger('study_id');
            $table->unsignedBigInteger('company_id');
            $table->timestamps();
            $table->unsignedBigInteger('microfinance_product_id');
            $table->boolean('is_funding_by_mtl')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('non_banking_service')->dropIfExists('microfinance_breakdowns');
    }
};
