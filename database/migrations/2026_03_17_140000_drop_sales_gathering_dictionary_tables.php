<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('sales_gathering_branches');
        Schema::dropIfExists('sales_gathering_products');
        Schema::dropIfExists('sales_gathering_sales_channels');
        Schema::dropIfExists('sales_gathering_principles');
    }

    public function down(): void
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

        Schema::create('sales_gathering_products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->boolean('is_existing')->default(true)->comment('is new product in financial planning study');
            $table->boolean('is_new')->default(false)->comment('is new product in financial planning study');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('study_id')->nullable();
            $table->timestamps();
        });

        Schema::create('sales_gathering_sales_channels', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->boolean('is_existing')->default(true)->comment('is new sales_channel in financial planning study');
            $table->boolean('is_new')->default(false)->comment('is new sales_channel in financial planning study');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('study_id')->nullable();
            $table->timestamps();
        });

        Schema::create('sales_gathering_principles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->boolean('is_existing')->default(true)->comment('is new principle in financial planning study');
            $table->boolean('is_new')->default(false)->comment('is new principle in financial planning study');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('study_id')->nullable();
            $table->timestamps();
        });
    }
};

