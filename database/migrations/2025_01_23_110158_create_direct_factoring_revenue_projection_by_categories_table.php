<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDirectFactoringRevenueProjectionByCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('non_banking_service')->create('direct_factoring_revenue_projection_by_categories', function (Blueprint $table) {
			$table->id();
			$table->json('growth_rates')->nullable();
			$table->json('direct_factoring_transactions_projections')->nullable();
			$table->unsignedBigInteger('study_id');
			$time = now()->toTimeString();
			$key = Str::uuid();
			$fullKey = $time.$key;
		// ref	$this->foreign('study_id','study_'.$fullKey)->references('id')->on('studies')->cascadeOnDelete();
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
    }
}
