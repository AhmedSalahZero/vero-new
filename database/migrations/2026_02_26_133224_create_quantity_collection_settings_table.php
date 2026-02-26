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
        Schema::create('quantity_collection_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('company_id')->nullable();
            $table->enum('collection_base', ['general_collection_policy', 'branch', 'business_sector', 'sales_channel', 'zone']);
            $table->longText('general_collection')->nullable();
            $table->longText('first_allocation_collection')->nullable();
            $table->longText('second_allocation_collection')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quantity_collection_settings');
    }
};
