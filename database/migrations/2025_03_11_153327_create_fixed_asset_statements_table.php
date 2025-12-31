<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFixedAssetStatementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->create('fixed_asset_statements', function (Blueprint $table) {
            $table->id();
			$table->json('beginning_balance')->nullable();
			$table->json('additions')->nullable();
			$table->json('initial_total_gross')->nullable();
			$table->json('replacement_cost')->nullable();
			$table->json('final_total_gross')->nullable();
			$table->json('total_monthly_depreciation')->nullable();
			$table->json('accumulated_depreciation')->nullable();
			$table->json('end_balance')->nullable();
			$table->unsignedBigInteger('fixed_asset_id');
			$table->unsignedBigInteger('study_id');
			$table->unsignedBigInteger('company_id');
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
        Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->dropIfExists('fixed_asset_statements');
    }
}
