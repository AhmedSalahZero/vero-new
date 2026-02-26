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
        Schema::connection('non_banking_service')->create('expenses', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('expense_name_id');
            $table->string('expense_category')->nullable();
            $table->string('category_name')->nullable();
            $table->integer('start_date')->nullable();
            $table->string('start_date_type')->nullable()->comment('start_date,operation_date in case of microfinance');
            $table->integer('end_date')->nullable()->comment('end date as index');
            $table->string('interval')->nullable();
            $table->string('monthly_cost_of_unit')->nullable();
            $table->string('percentage_of')->nullable();
            $table->longText('revenue_stream_type')->nullable();
            $table->longText('stream_category_ids')->nullable();
            $table->string('monthly_percentage')->nullable()->default('0');
            $table->string('payment_terms')->nullable();
            $table->longText('payment_amounts')->nullable();
            $table->longText('collection_statements')->nullable();
            $table->longText('withhold_statements')->nullable();
            $table->string('vat_rate')->nullable();
            $table->integer('is_deductible')->default(0);
            $table->string('withhold_tax_rate')->nullable();
            $table->string('increase_interval')->nullable();
            $table->decimal('amount', 14, 0)->default(0);
            $table->longText('monthly_repeating_amounts')->nullable();
            $table->longText('withhold_amounts')->nullable();
            $table->longText('vat_amounts')->nullable();
            $table->longText('expense_as_percentages')->nullable();
            $table->longText('total_vat')->nullable();
            $table->longText('total_after_vat')->nullable();
            $table->longText('withhold_payments')->nullable();
            $table->longText('net_payments_after_withhold')->nullable();
            $table->longText('sensitivity_expense_as_percentages')->nullable();
            $table->longText('payload')->nullable();
            $table->integer('model_id');
            $table->string('model_name')->nullable();
            $table->unsignedBigInteger('study_id')->nullable();
            $table->string('expense_type')->nullable();
            $table->string('relation_name')->nullable();
            $table->longText('custom_collection_policy')->nullable();
            $table->integer('company_id');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->nullable();
            $table->integer('amortization_months')->default(12);
            $table->longText('position_ids')->nullable();
            $table->longText('increase_rates')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('microfinance_allocation')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('non_banking_service')->dropIfExists('expenses');
    }
};
