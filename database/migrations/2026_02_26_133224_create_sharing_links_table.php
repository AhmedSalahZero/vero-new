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
        Schema::create('sharing_links', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('link');
            $table->string('identifier');
            $table->string('user_name')->nullable();
            $table->string('shareable_type');
            $table->unsignedBigInteger('shareable_id');
            $table->boolean('is_active');
            $table->double('number_of_views')->default(0);
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sharing_links');
    }
};
