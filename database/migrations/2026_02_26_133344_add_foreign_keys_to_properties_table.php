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
        Schema::connection('property_management')->table('properties', function (Blueprint $table) {
            $table->foreign(['parent_property_id'])->references(['id'])->on('properties')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('property_management')->table('properties', function (Blueprint $table) {
            $table->dropForeign('properties_parent_property_id_foreign');
        });
    }
};
