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
        Schema::table('financial_statement_able_item_main_item', function (Blueprint $table) {
            $table->foreign(['company_id'], 'company_id_income_statement_item_main_item')->references(['id'])->on('companies')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('financial_statement_able_item_main_item', function (Blueprint $table) {
            $table->dropForeign('company_id_income_statement_item_main_item');
        });
    }
};
