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
        Schema::connection('property_management')->create('forecasted_properties', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('counts');
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('type_id');
            $table->unsignedBigInteger('acquisition_date')->nullable();
            $table->decimal('area', 15)->default(0);
            $table->decimal('sqr_price', 15)->default(0);
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('study_id');
            $table->timestamps();
            $table->longText('rent_revenues')->nullable();
            $table->longText('rent_collections')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('property_management')->dropIfExists('forecasted_properties');
    }
};
