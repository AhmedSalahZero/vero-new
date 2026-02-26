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
        Schema::table('business_sectors', function (Blueprint $table) {
            $table->foreign(['company_id'], 'company_id_business_sectors')->references(['id'])->on('companies')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['creator_id'], 'creator_id_business_sectors')->references(['id'])->on('users')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_sectors', function (Blueprint $table) {
            $table->dropForeign('company_id_business_sectors');
            $table->dropForeign('creator_id_business_sectors');
        });
    }
};
