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
        Schema::connection('non_banking_service')->create('ijara_mortgage_breakdowns', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('installment_interval');
            $table->decimal('margin_rate', 14)->default(0);
            $table->decimal('sensitivity_margin_rate', 8, 5)->default(0);
            $table->unsignedBigInteger('grace_period')->nullable()->default(0);
            $table->double('tenor')->default(0);
            $table->longText('percentage_payload')->nullable();
            $table->longText('loan_amounts')->nullable();
            $table->longText('monthly_loan_amounts')->nullable();
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
        Schema::connection('non_banking_service')->dropIfExists('ijara_mortgage_breakdowns');
    }
};
