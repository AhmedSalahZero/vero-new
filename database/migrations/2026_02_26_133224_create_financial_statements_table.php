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
        Schema::create('financial_statements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('type')->default('actual');
            $table->string('duration');
            $table->enum('duration_type', ['monthly', 'annually', 'semi-annually', 'quarterly'])->default('monthly');
            $table->string('start_from');
            $table->unsignedBigInteger('company_id')->index('company_id_income_statements');
            $table->unsignedBigInteger('creator_id')->nullable()->index('creator_id_income_statements');
            $table->timestamps();
            $table->decimal('corporate_taxes_rate', 14, 5)->default(0);
            $table->decimal('salary_taxes_rate', 14, 5)->default(0);
            $table->decimal('social_insurance_rate', 14, 5)->default(0);
            $table->date('study_start_date')->nullable();
            $table->integer('duration_in_years')->default(0);
            $table->date('study_end_date')->nullable();
            $table->longText('study_dates')->nullable();
            $table->longText('operation_dates')->nullable();
            $table->double('operation_start_month')->nullable();
            $table->date('operation_start_date')->nullable();
            $table->string('financial_year_start_month')->default('january');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_statements');
    }
};
