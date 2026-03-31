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
        Schema::connection(PROPERTY_MANAGEMENT_CONNECTION_NAME)->table('forecasted_property_due_installments', function (Blueprint $table) {
            $table->integer('annual_start_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('forecasted_property_due_installments', function (Blueprint $table) {
            //
        });
    }
};
