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
        Schema::connection('non_banking_service')->create('revenue_contracts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('study_id');
            $table->unsignedBigInteger('company_id');
            $table->string('category_id')->nullable();
            $table->longText('monthly_loan_amounts')->nullable();
            $table->longText('contract_counts')->nullable();
            $table->string('revenue_type')->nullable();
            $table->unsignedBigInteger('leasing_breakdown_id')->nullable();
            $table->unsignedBigInteger('ijara_breakdown_id')->nullable();
            $table->unsignedBigInteger('reverse_breakdown_id')->nullable();
            $table->unsignedBigInteger('direct_breakdown_id')->nullable();
            $table->boolean('portfolio_mortgage_category_id')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('non_banking_service')->dropIfExists('revenue_contracts');
    }
};
