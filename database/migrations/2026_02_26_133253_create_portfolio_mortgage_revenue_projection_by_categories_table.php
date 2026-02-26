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
        Schema::connection('non_banking_service')->create('portfolio_mortgage_revenue_projection_by_categories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->decimal('monthly_margin_rate', 14)->default(0);
            $table->decimal('quarterly_margin_rate', 14)->default(0);
            $table->decimal('annually_margin_rate', 14)->default(0);
            $table->longText('monthly_due_cheques_percentages')->nullable();
            $table->longText('quarterly_due_cheques_percentages')->nullable();
            $table->longText('annually_due_cheques_percentages')->nullable();
            $table->longText('growth_rates')->nullable();
            $table->longText('portfolio_mortgage_transactions_projections')->nullable();
            $table->longText('frequency_per_year')->nullable();
            $table->longText('start_from')->nullable();
            $table->unsignedBigInteger('study_id');
            $table->unsignedBigInteger('company_id');
            $table->timestamps();
            $table->decimal('margin_rate', 14)->default(0);
            $table->integer('portfolio_mortgage_duration')->default(0);
            $table->longText('occurrence_dates')->nullable();
            $table->longText('statement')->nullable();
            $table->longText('loan_amounts')->nullable();
            $table->longText('total_monthly_amounts_per_years')->nullable();
            $table->longText('portfolio_mortgage_unearned_interest_statement')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('non_banking_service')->dropIfExists('portfolio_mortgage_revenue_projection_by_categories');
    }
};
