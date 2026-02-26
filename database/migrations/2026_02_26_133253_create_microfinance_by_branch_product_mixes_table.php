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
        Schema::connection('non_banking_service')->create('microfinance_by_branch_product_mixes', function (Blueprint $table) {
            $table->unsignedBigInteger('tenor')->default(0);
            $table->decimal('avg_amount', 14)->default(0);
            $table->integer('early_payment_installment_counts')->default(0);
            $table->string('funded_by');
            $table->unsignedBigInteger('microfinance_product_id');
            $table->longText('flat_rates')->nullable();
            $table->longText('decrease_rates')->nullable();
            $table->unsignedBigInteger('study_id');
            $table->unsignedBigInteger('company_id');
            $table->timestamps();
            $table->bigIncrements('id');
            $table->longText('increase_rates')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('non_banking_service')->dropIfExists('microfinance_by_branch_product_mixes');
    }
};
