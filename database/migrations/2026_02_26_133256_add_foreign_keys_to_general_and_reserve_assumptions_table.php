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
        Schema::connection('non_banking_service')->table('general_and_reserve_assumptions', function (Blueprint $table) {
            $table->foreign(['study_id'])->references(['id'])->on('studies')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('non_banking_service')->table('general_and_reserve_assumptions', function (Blueprint $table) {
            $table->dropForeign('general_and_reserve_assumptions_study_id_foreign');
        });
    }
};
