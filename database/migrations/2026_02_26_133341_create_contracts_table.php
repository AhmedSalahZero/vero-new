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
        Schema::connection('property_management')->create('contracts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('property_id');
            $table->decimal('monthly_rent', 15);
            $table->longText('rent_revenues')->nullable();
            $table->longText('rent_collections')->nullable();
            $table->decimal('variable_from_tenant_revenues_percentage', 15)->nullable();
            $table->decimal('min_amount', 15)->nullable();
            $table->string('contract_currency')->nullable();
            $table->string('collection_currency')->nullable();
            $table->date('contract_start_date');
            $table->date('contract_end_date');
            $table->enum('collection_interval', ['monthly', 'quarterly', 'semi-annually', 'annually']);
            $table->integer('insurance_months_count');
            $table->decimal('insurance_amount', 15);
            $table->enum('status', ['running', 'finished', 'expired'])->default('running');
            $table->date('finished_date')->nullable();
            $table->decimal('annually_increase_rate', 5)->nullable();
            $table->string('collection_policy')->nullable();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['property_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('property_management')->dropIfExists('contracts');
    }
};
