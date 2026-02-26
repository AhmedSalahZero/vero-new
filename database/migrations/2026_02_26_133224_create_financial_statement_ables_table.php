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
        Schema::create('financial_statement_ables', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('can_view_actual_report')->default(1);
            $table->boolean('is_caching_modified')->nullable()->default(false);
            $table->boolean('is_caching_adjusted')->nullable()->default(false);
            $table->boolean('is_caching_actual')->nullable()->default(false);
            $table->boolean('is_caching_forecast')->nullable()->default(false);
            $table->string('name');
            $table->string('duration');
            $table->string('type')->nullable()->default('IncomeStatement');
            $table->enum('duration_type', ['monthly', 'annually', 'semi-annually', 'quarterly'])->default('monthly');
            $table->string('start_from');
            $table->unsignedBigInteger('company_id')->index('company_id_income_statements');
            $table->unsignedBigInteger('creator_id')->nullable()->index('creator_id_income_statements');
            $table->unsignedBigInteger('financial_statement_id')->nullable();
            $table->string('cash_and_banks_beginning_balance')->nullable();
            $table->timestamps();
            $table->string('entered_receivables_and_payments_table')->nullable()->default('0');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_statement_ables');
    }
};
