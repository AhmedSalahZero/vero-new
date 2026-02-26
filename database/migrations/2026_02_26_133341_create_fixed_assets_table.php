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
        Schema::connection('property_management')->create('fixed_assets', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('name_id')->nullable();
            $table->string('type')->default('ffe');
            $table->decimal('ffe_item_cost', 14, 0)->default(0);
            $table->decimal('vat_rate', 14)->nullable();
            $table->decimal('withhold_tax_rate', 14)->nullable();
            $table->decimal('contingency_rate', 14)->default(0);
            $table->decimal('cost_annual_increase_rate', 14)->default(0);
            $table->string('payment_terms')->nullable();
            $table->longText('custom_collection_policy')->nullable();
            $table->integer('depreciation_duration')->default(0);
            $table->decimal('replacement_cost_rate', 14)->default(0);
            $table->integer('replacement_interval')->default(1);
            $table->integer('counts')->default(0);
            $table->longText('ffe_counts')->nullable();
            $table->longText('monthly_amounts')->nullable();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('study_id');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->nullable();
            $table->longText('department_ids')->nullable();
            $table->longText('position_ids')->nullable();
            $table->longText('statement')->nullable();
            $table->longText('ffe_equity_payment')->nullable();
            $table->longText('ffe_loan_withdrawal')->nullable();
            $table->longText('loan_capitalized_interests')->nullable();
            $table->longText('income_statement_loan_capitalized_interests')->nullable();
            $table->longText('ffe_loan_withdrawal_end_balance')->nullable();
            $table->longText('depreciation_statement')->nullable();
            $table->longText('total_monthly_depreciations')->nullable();
            $table->longText('capitalization_statement')->nullable();
            $table->longText('ffe_execution_and_payment')->nullable();
            $table->longText('ffe_payable')->nullable();
            $table->longText('ffe_payment')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('property_management')->dropIfExists('fixed_assets');
    }
};
