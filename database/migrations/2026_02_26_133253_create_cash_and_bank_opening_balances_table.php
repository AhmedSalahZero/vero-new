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
        Schema::connection('non_banking_service')->create('cash_and_bank_opening_balances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->decimal('ecl_existing_rate', 14)->default(0);
            $table->decimal('cash_and_bank_amount', 14)->default(0);
            $table->decimal('customer_receivable_amount', 14)->default(0);
            $table->decimal('expected_credit_loss', 14)->default(0);
            $table->longText('ecl_existing_expenses')->nullable();
            $table->longText('accumulated_ecl_existing_expenses')->nullable();
            $table->longText('interests')->nullable();
            $table->longText('payload')->nullable();
            $table->unsignedBigInteger('study_id');
            $table->timestamps();
            $table->longText('statement')->nullable()->comment('(DC2Type:json)');
            $table->decimal('non_performing_outstanding', 14)->default(0);
            $table->decimal('non_performing_ecl_existing_rate', 14)->default(0);
            $table->decimal('non_performing_expected_credit_loss', 14)->default(0);
            $table->json('non_performing_payload')->nullable();
            $table->json('non_performing_interests')->nullable();
            $table->json('non_performing_ecl_existing_expenses')->nullable();
            $table->json('non_performing_accumulated_ecl_existing_expenses')->nullable();
            $table->json('non_performing_statement')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('non_banking_service')->dropIfExists('cash_and_bank_opening_balances');
    }
};
