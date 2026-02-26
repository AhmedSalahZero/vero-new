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
        Schema::create('others', function (Blueprint $table) {
            $table->increments('id');
            $table->string('collection_policy_type')->nullable();
            $table->string('collection_policy_value');
            $table->string('collection_policy_interval')->nullable();
            $table->bigInteger('other_type_id')->nullable();
            $table->string('f&b_facilities')->nullable();
            $table->bigInteger('other_count')->nullable();
            $table->string('chosen_other_currency')->nullable();
            $table->bigInteger('hospitality_sector_id');
            $table->string('charges_per_guest_escalation_rate')->nullable();
            $table->string('charges_per_guest')->nullable();
            $table->string('charges_per_guest_annual_escalation_rate')->nullable();
            $table->string('charges_per_guest_at_operation_date')->nullable();
            $table->longText('guest_capture_cover_percentage')->nullable();
            $table->longText('percentage_from_rooms_revenues')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('others');
    }
};
