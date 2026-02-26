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
        Schema::connection('property_management')->create('income_statement_reports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->longText('existing_loans_interests_expense')->nullable();
            $table->longText('fixed_asset_loan_interest_expenses')->nullable();
            $table->longText('manpower_expenses')->nullable();
            $table->longText('total_manpower_expenses')->nullable();
            $table->longText('depreciation_expenses')->nullable();
            $table->longText('opening_depreciation_expenses')->nullable();
            $table->longText('oda_interests')->nullable()->comment('(DC2Type:json)');
            $table->longText('cost-of-service')->nullable();
            $table->longText('total_cost-of-service')->nullable();
            $table->longText('marketing-expense')->nullable();
            $table->longText('total_marketing-expense')->nullable();
            $table->longText('other-operation-expense')->nullable();
            $table->longText('total_other-operation-expense')->nullable();
            $table->longText('sales-expense')->nullable();
            $table->longText('total_sales-expense')->nullable();
            $table->longText('general-expense')->nullable();
            $table->longText('total_general-expense')->nullable();
            $table->longText('corporate_taxes')->nullable();
            $table->unsignedBigInteger('study_id');
            $table->unsignedBigInteger('company_id');
            $table->timestamps();
            $table->longText('interest_cash_surplus')->nullable();
            $table->longText('corporate_taxes_end_balance')->nullable();
            $table->json('full_coverage_rent_revenues')->nullable();
            $table->json('partial_coverage_rent_revenues')->nullable();
            $table->json('to_be_delivered_rent_revenues')->nullable();
            $table->json('property_forecasted_rent_revenues')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('property_management')->dropIfExists('income_statement_reports');
    }
};
