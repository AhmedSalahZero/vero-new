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
        Schema::create('serviceables', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('serviceable_type');
            $table->unsignedBigInteger('serviceable_id');
            $table->unsignedBigInteger('revenue_business_line_id')->index('serviceables_revenue_business_line_id_foreign');
            $table->unsignedBigInteger('service_category_id')->index('serviceables_service_category_id_foreign');
            $table->unsignedBigInteger('service_item_id')->index('serviceables_service_item_id_foreign');
            $table->unsignedBigInteger('service_nature_id')->index('serviceables_service_nature_id_foreign');
            $table->double('delivery_days')->default(0);
            $table->timestamps();

            $table->index(['serviceable_type', 'serviceable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('serviceables');
    }
};
