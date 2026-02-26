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
        Schema::connection('property_management')->create('property_contract_partial_rent_renewals', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('study_id');
            $table->unsignedBigInteger('property_id');
            $table->unsignedBigInteger('contract_id')->nullable();
            $table->string('collection_interval')->nullable()->default('monthly');
            $table->longText('rent_revenues')->nullable();
            $table->longText('rent_collections')->nullable();
            $table->unsignedBigInteger('company_id');
            $table->unsignedInteger('renewal_type')->nullable()->comment('1 for renew or 2 for renovate-and-revenue');
            $table->unsignedBigInteger('renovate_duration')->default(0);
            $table->decimal('renovate_cost', 14)->default(0);
            $table->decimal('renewal_increase_rate', 12)->default(0);
            $table->decimal('new_rent_amount', 14)->default(0);
            $table->unsignedBigInteger('renewal_duration')->default(0);
            $table->decimal('renewal_annual_increase', 12)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('property_management')->dropIfExists('property_contract_partial_rent_renewals');
    }
};
