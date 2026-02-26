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
        Schema::create('sales_gathering_branches', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->boolean('is_existing')->default(true)->comment('is new branch in financial planning study');
            $table->boolean('is_new')->default(false)->comment('is new branch in financial planning study');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('study_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_gathering_branches');
    }
};
