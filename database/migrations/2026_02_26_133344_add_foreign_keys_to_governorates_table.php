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
        Schema::connection('property_management')->table('governorates', function (Blueprint $table) {
            $table->foreign(['country_id'])->references(['id'])->on('countries')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('property_management')->table('governorates', function (Blueprint $table) {
            $table->dropForeign('governorates_country_id_foreign');
        });
    }
};
