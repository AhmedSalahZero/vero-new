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
        Schema::create('financial_statement_able_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->boolean('has_sub_items')->default(true);
            $table->boolean('has_depreciation_or_amortization')->default(false);
            $table->boolean('has_percentage_or_fixed_sub_items');
            $table->string('financial_statement_able_type')->default('IncomeStatement');
            $table->boolean('is_main_for_all_calculations')->default(false);
            $table->boolean('is_sales_rate')->default(false);
            $table->boolean('for_interval_comparing')->default(true);
            $table->longText('depends_on')->nullable()->comment('auto-calculated');
            $table->string('equation')->nullable();
            $table->boolean('has_auto_depreciation')->default(false);
            $table->integer('is_auto_depreciation_for')->default(0);
            $table->boolean('is_accumulated')->nullable()->default(false);
            $table->integer('has_vat_rate')->nullable()->default(0);
            $table->integer('can_be_dedictiable')->nullable()->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_statement_able_items');
    }
};
