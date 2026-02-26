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
        Schema::connection('property_management')->create('studies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->decimal('revenue_multiplier', 14)->default(0);
            $table->decimal('ebitda_multiplier', 14)->default(0);
            $table->decimal('cost_of_equity_rate', 14)->default(0);
            $table->string('name')->comment('اسم الدراسة');
            $table->string('company_nature')->comment('نوع الشركة ');
            $table->date('study_start_date');
            $table->integer('duration_in_years');
            $table->date('study_end_date');
            $table->double('operation_start_month');
            $table->date('operation_start_date');
            $table->string('financial_year_start_month');
            $table->decimal('corporate_taxes_rate', 14)->default(0);
            $table->decimal('salary_taxes_rate', 14)->default(0);
            $table->decimal('social_insurance_rate', 14)->default(0);
            $table->decimal('perpetual_growth_rate', 14)->default(0);
            $table->decimal('shareholder_equity_multiplier', 14)->default(0);
            $table->longText('operation_dates')->nullable();
            $table->longText('study_dates')->nullable();
            $table->unsignedInteger('consumerfinance_branches_count')->default(0);
            $table->unsignedBigInteger('company_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('property_management')->dropIfExists('studies');
    }
};
