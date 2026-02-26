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
        Schema::connection('non_banking_service')->create('securitization_loan_schedules', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('securitization_id');
            $table->decimal('portfolio_disbursement_amount', 14)->default(0);
            $table->decimal('portfolio_schedule_payment_sum', 14)->default(0);
            $table->decimal('net_present_value', 14)->default(0);
            $table->decimal('bank_portfolio_end_balance_sum', 14);
            $table->decimal('securitization_profit_or_loss', 14)->default(0);
            $table->longText('collection_revenue_amounts')->nullable();
            $table->decimal('early_settlements_expense_amount', 14)->default(0);
            $table->decimal('securitization_expense_amount', 14)->default(0);
            $table->unsignedBigInteger('study_id');
            $table->unsignedBigInteger('company_id');
            $table->timestamps();
            $table->decimal('portfolio_principle_amount_sum', 14)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('non_banking_service')->dropIfExists('securitization_loan_schedules');
    }
};
