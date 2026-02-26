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
        Schema::connection('non_banking_service')->create('consumerfinance_product_sales_projects', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('consumerfinance_product_id');
            $table->unsignedBigInteger('tenor')->default(0);
            $table->decimal('avg_amount', 14)->default(0);
            $table->longText('monthly_amounts')->nullable();
            $table->string('funded_by')->default('0')->comment('by-odas , by-mtls');
            $table->longText('flat_rates')->nullable();
            $table->longText('decrease_rates')->nullable();
            $table->longText('loan_amounts')->nullable();
            $table->longText('monthly_loan_amounts')->nullable();
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
        Schema::connection('non_banking_service')->dropIfExists('consumerfinance_product_sales_projects');
    }
};
