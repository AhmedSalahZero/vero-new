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
        Schema::connection('property_management')->create('supplier_payable_opening_balances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->decimal('amount', 14)->default(0);
            $table->longText('payload')->nullable();
            $table->unsignedBigInteger('study_id');
            $table->timestamps();
            $table->longText('statement')->nullable()->comment('(DC2Type:json)');
            $table->decimal('odas_outstanding_opening_amount', 14, 5)->default(0);
            $table->longText('portfolio_interest_expenses')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('property_management')->dropIfExists('supplier_payable_opening_balances');
    }
};
