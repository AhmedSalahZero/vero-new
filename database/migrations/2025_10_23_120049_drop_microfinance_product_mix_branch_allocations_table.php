<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropMicrofinanceProductMixBranchAllocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->drop('microfinance_product_mix_branch_allocations');
        Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->create('microfinance_by_branch_product_mixes', function (Blueprint $table) {
            $table->unsignedBigInteger('tenor')->default(0);
            $table->decimal('avg_amount', 14, 2)->default(0);
            $table->unsignedBigInteger('microfinance_product_id');
            $table->json('flat_rates')->nullable();
            $table->json('decrease_rates')->nullable();
           
            $table->studyFields();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
