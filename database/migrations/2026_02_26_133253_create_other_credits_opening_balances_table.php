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
        Schema::connection('non_banking_service')->create('other_credits_opening_balances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->nullable();
            $table->decimal('amount', 14)->default(0);
            $table->longText('payload')->nullable();
            $table->unsignedBigInteger('study_id');
            $table->timestamps();
            $table->longText('statement')->nullable()->comment('(DC2Type:json)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('non_banking_service')->dropIfExists('other_credits_opening_balances');
    }
};
