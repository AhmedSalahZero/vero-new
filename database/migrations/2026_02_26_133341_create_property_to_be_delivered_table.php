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
        Schema::connection('property_management')->create('property_to_be_delivered', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('study_id');
            $table->unsignedBigInteger('property_id');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('renovate_duration')->default(0);
            $table->decimal('renovate_cost', 14)->default(0);
            $table->decimal('monthly_rent_amount', 14)->default(0);
            $table->string('collection_interval')->nullable();
            $table->unsignedBigInteger('rent_duration')->default(0);
            $table->decimal('rent_annual_increase', 12)->default(0);
            $table->timestamps();
            $table->json('rent_revenues')->nullable();
            $table->json('rent_collections')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('property_management')->dropIfExists('property_to_be_delivered');
    }
};
