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
        Schema::create('profitabilities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('profitabilityAble_type');
            $table->unsignedBigInteger('profitabilityAble_id');
            $table->double('percentage')->default(0);
            $table->double('net_profit_after_taxes')->default(0);
            $table->double('vat')->default(0);
            $table->unsignedBigInteger('company_id')->index('company_id_profitabilities');
            $table->unsignedBigInteger('creator_id')->nullable()->index('creator_id_profitabilities');
            $table->timestamps();

            $table->index(['profitabilityAble_type', 'profitabilityAble_id'], 'profitable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profitabilities');
    }
};
