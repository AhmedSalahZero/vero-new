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
        Schema::connection(PROPERTY_MANAGEMENT_CONNECTION_NAME)->table('forecasted_properties', function (Blueprint $table) {
			if (!Schema::connection(PROPERTY_MANAGEMENT_CONNECTION_NAME)->hasColumn('forecasted_properties', 'renovate_duration')) {
            $table->unsignedBigInteger('renovate_duration')->default(0);
            $table->decimal('renovate_cost', 14)->default(0);
				$table->decimal('monthly_rent_amount', 14)->default(0);
				$table->string('collection_interval')->nullable();
				$table->unsignedBigInteger('rent_duration')->default(0);
				$table->decimal('rent_annual_increase', 12)->default(0);
			}
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection(PROPERTY_MANAGEMENT_CONNECTION_NAME)->table('forecasted_properties', function (Blueprint $table) {
            $table->dropColumn([
                'renovate_duration',
                'renovate_cost',
                'monthly_rent_amount',
                'collection_interval',
                'rent_duration',
                'rent_annual_increase',
            ]);
        });
    }
};
