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
        Schema::connection('property_management')->create('loan_schedule_payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('revenue_stream_type')->nullable()->comment('leasing or factoring or ...');
            $table->string('portfolio_loan_type')->nullable()->comment('bank_portfolio or portfolio');
            $table->unsignedBigInteger('revenue_stream_id')->nullable()->comment('LeasingRevenueStreamBreakdown');
            $table->unsignedBigInteger('revenue_stream_category_id')->nullable()->comment('LeasingCategory');
            $table->integer('month_as_index');
            $table->string('loan_type')->nullable();
            $table->longText('totals')->nullable();
            $table->longText('beginning');
            $table->longText('interestAmount');
            $table->longText('interestCorridorChangeStatement')->nullable();
            $table->longText('schedulePayment');
            $table->longText('accured_interest')->nullable();
            $table->longText('InterestPayment')->nullable();
            $table->longText('principleAmount');
            $table->longText('endBalance');
            $table->unsignedBigInteger('securitization_date_index')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('study_id');
            $table->unsignedBigInteger('company_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('property_management')->dropIfExists('loan_schedule_payments');
    }
};
