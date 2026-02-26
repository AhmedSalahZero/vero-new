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
        Schema::connection('property_management')->create('fixed_assets_loan_schedule_payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('fixed_asset_id')->nullable();
            $table->string('fixed_asset_type');
            $table->string('portfolio_loan_type')->nullable()->comment('bank_portfolio or portfolio');
            $table->integer('month_as_index');
            $table->string('loan_type')->nullable();
            $table->longText('totals')->nullable();
            $table->longText('beginning');
            $table->longText('interestAmount');
            $table->longText('schedulePayment');
            $table->longText('principleAmount');
            $table->longText('endBalance');
            $table->unsignedBigInteger('study_id');
            $table->unsignedBigInteger('company_id');
            $table->timestamps();
            $table->longText('accured_interest')->nullable();
            $table->longText('no_securitization')->nullable();
            $table->decimal('interest_rate', 14)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('property_management')->dropIfExists('fixed_assets_loan_schedule_payments');
    }
};
