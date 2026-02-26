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
        Schema::connection('property_management')->create('properties', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('location')->nullable();
            $table->string('code')->nullable();
            $table->string('nature_id')->nullable()->default('unit');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('type_id')->nullable();
            $table->unsignedBigInteger('ownership_id')->nullable();
            $table->decimal('area', 14)->nullable();
            $table->string('unit_of_measurement')->nullable();
            $table->decimal('acquisition_cost', 14)->default(0);
            $table->date('acquisition_date')->nullable();
            $table->decimal('current_book_value', 14)->default(0);
            $table->date('book_value_date')->nullable();
            $table->json('depreciations')->nullable();
            $table->decimal('month_depreciation', 14)->default(0);
            $table->unsignedBigInteger('duration_in_months')->default(0);
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('country_id')->nullable();
            $table->unsignedBigInteger('governorate_id')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('parent_property_id')->nullable()->index('properties_parent_property_id_foreign')->comment('For units inside complex/building');
            $table->longText('tax_rates')->nullable();
            $table->longText('market_values')->nullable();

            $table->unique(['code', 'company_id'], 'properties_code_company_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('property_management')->dropIfExists('properties');
    }
};
