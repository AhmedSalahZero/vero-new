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
        Schema::create('financial_statement_able_main_item_sub_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('vat_rate')->nullable()->default('0');
            $table->integer('is_deductible')->nullable()->default(0);
            $table->string('is_value_quantity_price')->nullable()->default('value');
            $table->unsignedBigInteger('financial_statement_able_id')->index('income_statement_foreign2');
            $table->unsignedBigInteger('financial_statement_able_item_id')->index('income_report_id2');
            $table->string('sub_item_name')->nullable()->comment('when null it stores the main row data that has no sub rows');
            $table->string('sub_item_type')->default('actual');
            $table->string('receivable_or_payment')->nullable();
            $table->integer('ordered')->default(2);
            $table->string('created_from')->default('forecast');
            $table->longText('payload')->nullable();
            $table->decimal('total', 14)->default(0);
            $table->longText('actual_dates')->nullable();
            $table->boolean('is_depreciation_or_amortization')->nullable()->default(false);
            $table->tinyInteger('has_collection_policy')->default(0);
            $table->string('collection_policy_type')->nullable();
            $table->string('collection_policy_value')->nullable();
            $table->boolean('is_quantity')->nullable()->default(false);
            $table->boolean('can_be_quantity');
            $table->tinyInteger('can_be_percentage_or_fixed')->default(1);
            $table->unsignedBigInteger('company_id')->index('company_id_income_statement_main_item_sub_items');
            $table->string('percentage_or_fixed')->default('fixed');
            $table->longText('is_percentage_of')->nullable();
            $table->string('repeating_fixed_value')->nullable()->default('0');
            $table->unsignedBigInteger('creator_id')->nullable()->index('creator_id_income_statement_main_item_sub_items');
            $table->string('percentage_value')->nullable()->default('0');
            $table->longText('is_cost_of_unit_of')->nullable();
            $table->string('cost_of_unit_value')->nullable()->default('0');
            $table->boolean('is_financial_expense')->nullable()->default(false);
            $table->string('is_financial_income')->nullable()->default('0');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_statement_able_main_item_sub_items');
    }
};
