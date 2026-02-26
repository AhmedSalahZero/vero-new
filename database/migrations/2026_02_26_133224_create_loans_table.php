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
        Schema::create('loans', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id');
            $table->string('section_name')->nullable();
            $table->integer('is_with_capitalization')->nullable()->default(0);
            $table->integer('financial_institution_id')->nullable()->default(0);
            $table->string('model_type')->nullable();
            $table->string('loan_type')->nullable();
            $table->string('grace_period')->nullable();
            $table->string('loan_amount')->nullable();
            $table->string('installment_interval')->nullable();
            $table->string('start_date')->nullable();
            $table->string('end_date')->nullable();
            $table->string('period')->nullable();
            $table->string('fixedType')->nullable();
            $table->string('base_rate')->nullable();
            $table->string('margin_rate')->nullable();
            $table->string('pricing')->nullable();
            $table->string('duration')->nullable()->comment('tenor');
            $table->string('step_down_rate')->nullable();
            $table->string('step_up_rate')->nullable();
            $table->string('step_up_interval')->nullable();
            $table->string('step_down_interval')->nullable();
            $table->string('borrowing_rate')->nullable();
            $table->string('capitalization_type')->nullable();
            $table->string('margin_interest')->nullable();
            $table->string('loan_interest')->nullable();
            $table->string('min_interest')->nullable();
            $table->string('repayment_duration')->nullable();
            $table->string('installment_amount')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->nullable();
            $table->string('interest_interval')->nullable()->default('monthly');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
