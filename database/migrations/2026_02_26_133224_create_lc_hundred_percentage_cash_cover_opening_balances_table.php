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
        Schema::create('lc_hundred_percentage_cash_cover_opening_balances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('currency');
            $table->string('lc_type');
            $table->integer('financial_institution_id')->index('lc__fname');
            $table->unsignedBigInteger('lc_opening_balance_id')->index('lc__opname');
            $table->date('lc_expiry_date');
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
        Schema::dropIfExists('lc_hundred_percentage_cash_cover_opening_balances');
    }
};
