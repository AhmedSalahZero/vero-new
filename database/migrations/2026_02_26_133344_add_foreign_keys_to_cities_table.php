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
        Schema::connection('property_management')->table('cities', function (Blueprint $table) {
            $table->foreign(['governorate_id'])->references(['id'])->on('governorates')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('property_management')->table('cities', function (Blueprint $table) {
            $table->dropForeign('cities_governorate_id_foreign');
        });
    }
};
