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
        Schema::connection('non_banking_service')->create('vat_and_credit_withhold_tax_opening_balances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->decimal('vat_amount', 14)->default(0);
            $table->decimal('credit_withhold_taxes', 14)->default(0);
            $table->unsignedBigInteger('study_id');
            $table->timestamps();
            $table->decimal('corporate_taxes_payable', 14)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('non_banking_service')->dropIfExists('vat_and_credit_withhold_tax_opening_balances');
    }
};
