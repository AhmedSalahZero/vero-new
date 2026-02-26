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
        Schema::create('financial_statement_able_item_main_item', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('financial_statement_able_id')->index('income_statement_foreign');
            $table->unsignedBigInteger('financial_statement_able_item_id')->index('income_report_id');
            $table->unsignedBigInteger('company_id')->index('company_id_income_statement_item_main_item');
            $table->unsignedBigInteger('creator_id')->nullable()->index('creator_id_income_statement_item_main_item');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_statement_able_item_main_item');
    }
};
