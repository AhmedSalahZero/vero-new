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
        Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('consumerfinance_product_sales_projects', function (Blueprint $table) {
            $table->json('increase_rates')->after('decrease_rates')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consumerfinance_product_sales_projects', function (Blueprint $table) {
            //
        });
    }
};
