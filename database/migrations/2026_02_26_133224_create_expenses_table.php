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
        Schema::create('expenses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('category_name')->nullable();
            $table->string('start_date')->nullable();
            $table->string('interval')->nullable();
            $table->string('monthly_cost_of_unit')->nullable();
            $table->string('revenue_stream_type')->nullable();
            $table->string('monthly_amount')->nullable();
            $table->string('month_percentage')->nullable()->default('0');
            $table->string('payment_terms')->nullable();
            $table->string('vat_rate')->nullable();
            $table->integer('is_deductible')->default(0);
            $table->string('withhold_tax_rate')->nullable();
            $table->string('increase_rate')->nullable();
            $table->string('increase_interval')->nullable();
            $table->longText('payload')->nullable();
            $table->integer('model_id');
            $table->string('model_name')->nullable();
            $table->string('relation_name')->nullable();
            $table->string('allocation_base_1')->nullable();
            $table->string('allocation_base_2')->nullable();
            $table->string('allocation_base_3')->nullable();
            $table->string('conditional_to')->nullable();
            $table->string('conditional_value_a')->nullable();
            $table->string('conditional_value_b')->nullable();
            $table->longText('custom_collection_policy')->nullable();
            $table->integer('company_id');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
