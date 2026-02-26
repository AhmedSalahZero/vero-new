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
        Schema::create('inventory_statement_tests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('company_id')->nullable();
            $table->string('type')->nullable();
            $table->string('date')->nullable();
            $table->string('document_num')->nullable();
            $table->string('name')->nullable();
            $table->string('category')->nullable();
            $table->string('local_or_imported')->nullable();
            $table->string('sub_category')->nullable();
            $table->string('product')->nullable();
            $table->string('product_sku')->nullable();
            $table->string('measurment_unit')->nullable();
            $table->string('beginning_balance')->nullable();
            $table->string('volume_in')->nullable();
            $table->string('volume_out')->nullable();
            $table->string('end_balance')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('created_by')->nullable();
            $table->longText('validation')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_statement_tests');
    }
};
