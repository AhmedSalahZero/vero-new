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
        Schema::connection(PROPERTY_MANAGEMENT_CONNECTION_NAME)->table('income_statement_reports', function (Blueprint $table) {
            $table->json('interest_corridor_changes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('income_statement_reports', function (Blueprint $table) {
            //
        });
    }
};
