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
        Schema::create('monthly_customer_invoices', function (Blueprint $table) {
            $table->integer('id', true);
            $table->unsignedInteger('company_id');
            $table->string('customer_name');
            $table->integer('month');
            $table->integer('year');
            $table->double('beginning_balance')->default(0);
            $table->double('monthly_debit')->default(0);
            $table->integer('monthly_credit')->default(0);
            $table->double('end_balance')->default(0);
            $table->boolean('is_closed')->default(false);
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_customer_invoices');
    }
};
