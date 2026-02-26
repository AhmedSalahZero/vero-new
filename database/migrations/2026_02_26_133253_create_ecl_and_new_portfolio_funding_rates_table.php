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
        Schema::connection('non_banking_service')->create('ecl_and_new_portfolio_funding_rates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('revenue_stream_type')->comment('leasing,factoring,...etc');
            $table->longText('admin_fees_rates')->nullable();
            $table->longText('monthly_admin_fees_amounts')->nullable();
            $table->longText('ecl_rates')->nullable();
            $table->longText('equity_funding_rates')->nullable();
            $table->longText('equity_funding_values')->nullable();
            $table->longText('new_loans_funding_rates')->nullable();
            $table->longText('new_loans_funding_values')->nullable();
            $table->longText('monthly_new_loans_funding_values')->nullable();
            $table->longText('monthly_new_odas_funding_values')->nullable();
            $table->unsignedBigInteger('study_id');
            $table->unsignedBigInteger('company_id');
            $table->timestamps();
            $table->longText('monthly_ecl_values')->nullable();
            $table->longText('accumulated_ecl_values')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('non_banking_service')->dropIfExists('ecl_and_new_portfolio_funding_rates');
    }
};
