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
        Schema::connection('non_banking_service')->create('securitizations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('revenue_stream_type');
            $table->unsignedInteger('disbursement_date');
            $table->unsignedInteger('securitization_date');
            $table->decimal('discount_rate', 14)->default(0);
            $table->decimal('collection_revenue_rate', 14)->default(0);
            $table->decimal('early_settlements_expense_rate', 14)->default(0);
            $table->decimal('expense_amount', 14)->default(0);
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
        Schema::connection('non_banking_service')->dropIfExists('securitizations');
    }
};
