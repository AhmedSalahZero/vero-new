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
        Schema::connection('non_banking_service')->create('microfinance_product_sales_projects', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type')->comment('all-branches , new-branches,by-branch');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('microfinance_product_id');
            $table->unsignedBigInteger('tenor')->default(0);
            $table->decimal('avg_amount', 14)->default(0);
            $table->integer('early_payment_installment_counts')->default(0);
            $table->longText('monthly_amounts')->nullable();
            $table->string('funded_by')->default('0')->comment('by-odas , by-mtls');
            $table->longText('product_mixes')->nullable();
            $table->longText('monthly_product_mixes')->nullable();
            $table->longText('monthly_loan_amounts')->nullable();
            $table->longText('total_cases_counts')->nullable();
            $table->longText('seasonality')->nullable();
            $table->longText('monthly_seasonality')->nullable();
            $table->longText('flat_rates')->nullable();
            $table->longText('decrease_rates')->nullable();
            $table->longText('setup_fees_durations')->nullable();
            $table->longText('fees_rates')->nullable();
            $table->unsignedBigInteger('study_id');
            $table->unsignedBigInteger('company_id');
            $table->timestamps();
            $table->longText('increase_rates')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('non_banking_service')->dropIfExists('microfinance_product_sales_projects');
    }
};
