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
        Schema::connection('non_banking_service')->create('lease_rent_liability_opening_opening_balances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('study_id')->index();
            $table->string('name')->nullable();
            $table->decimal('amount', 10);
            $table->json('rent_payment')->nullable();
            $table->json('rent_interest')->nullable();
            $table->json('statement');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('non_banking_service')->dropIfExists('lease_rent_liability_opening_opening_balances');
    }
};
