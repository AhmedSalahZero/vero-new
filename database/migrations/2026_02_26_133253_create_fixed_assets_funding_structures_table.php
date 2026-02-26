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
        Schema::connection('non_banking_service')->create('fixed_assets_funding_structures', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('fixed_asset_type');
            $table->boolean('is_fully_funded_though_equity')->default(false);
            $table->longText('direct_ffe_amounts')->nullable();
            $table->longText('equity_funding_rates')->nullable();
            $table->longText('equity_funding_values')->nullable();
            $table->longText('new_loans_funding_rates')->nullable();
            $table->longText('new_loans_funding_values')->nullable();
            $table->longText('tenors')->nullable();
            $table->longText('grace_periods')->nullable();
            $table->longText('interest_rates')->nullable();
            $table->longText('installment_intervals')->nullable();
            $table->unsignedBigInteger('study_id');
            $table->unsignedBigInteger('company_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('non_banking_service')->dropIfExists('fixed_assets_funding_structures');
    }
};
