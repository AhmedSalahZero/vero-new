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
        Schema::create('lg_hundred_percentage_cash_cover_opening_balances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('currency');
            $table->string('lg_type');
            $table->integer('financial_institution_id')->index('lg__fname');
            $table->unsignedBigInteger('lg_opening_balance_id')->index('lg__opname');
            $table->date('lg_expiry_date');
            $table->string('current_account_number');
            $table->decimal('amount', 20, 5)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lg_hundred_percentage_cash_cover_opening_balances');
    }
};
