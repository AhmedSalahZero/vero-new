<?php

use function PHPSTORM_META\map;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

use Illuminate\Support\Facades\Schema;

class CreateMicrofinanceProductMixBranchAllocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->create('microfinance_product_mix_branch_allocations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('microfinance_product_id');
            foreach ([
                'existing',
                'new'
            ] as $prefix) {
                $table->integer($prefix.'_branch_tenor')->default(0);
                $table->decimal($prefix.'_avg_amount', 14, 2)->default(0);
                $table->json($prefix.'_allocations')->nullable();
                $table->json($prefix.'_names')->nullable();
            }
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
    }
}
