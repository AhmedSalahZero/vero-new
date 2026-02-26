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
        Schema::connection('property_management')->create('forecasted_property_due_installments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('property_id')->index();
            $table->unsignedInteger('delivery_date')->nullable();
            $table->unsignedInteger('ready_to_use_date')->nullable();
            $table->enum('installment_type', ['regular', 'variable']);
            $table->decimal('signing_payment', 15)->nullable();
            $table->unsignedInteger('signing_payment_date')->nullable();
            $table->decimal('reservation_payment', 15)->nullable();
            $table->unsignedInteger('reservation_payment_date')->nullable();
            $table->longText('regular_installments_amounts')->nullable();
            $table->unsignedInteger('annual_date')->nullable();
            $table->decimal('annual_amount', 15)->nullable();
            $table->integer('annual_count')->nullable();
            $table->unsignedInteger('delivery_payments_start_date')->nullable();
            $table->decimal('delivery_payments_amount', 15)->nullable();
            $table->integer('delivery_payments_count')->nullable();
            $table->string('delivery_payments_payment_interval')->nullable();
            $table->unsignedInteger('maintenance_payments_start_date')->nullable();
            $table->decimal('maintenance_payments_amount', 15)->nullable();
            $table->integer('maintenance_payments_count')->nullable();
            $table->string('maintenance_payments_payment_interval')->nullable();
            $table->longText('variable_installment_amounts')->nullable();
            $table->longText('total_due_installments')->nullable();
            $table->boolean('has_annually_installments')->default(false);
            $table->boolean('has_delivery_payments')->default(false);
            $table->boolean('has_maintenance_payments')->default(false);
            $table->unsignedBigInteger('renovate_duration')->default(0);
            $table->decimal('renovate_cost', 14)->default(0);
            $table->decimal('monthly_rent_amount', 14)->default(0);
            $table->string('collection_interval')->nullable();
            $table->unsignedBigInteger('rent_duration')->default(0);
            $table->decimal('rent_annual_increase', 12)->default(0);
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('study_id');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('property_management')->dropIfExists('forecasted_property_due_installments');
    }
};
