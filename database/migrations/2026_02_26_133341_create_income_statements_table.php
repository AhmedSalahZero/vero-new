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
        Schema::connection('property_management')->create('income_statements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->longText('accumulated_retained_earnings')->nullable();
            $table->longText('monthly_corporate_taxes_statements')->nullable();
            $table->longText('monthly_net_profit')->nullable();
            $table->unsignedBigInteger('study_id');
            $table->timestamps();
            $table->longText('ebit')->nullable();
            $table->longText('total_depreciation')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('property_management')->dropIfExists('income_statements');
    }
};
