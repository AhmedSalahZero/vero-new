<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePortfolioMortgageRevenueProjectionByCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('non_banking_service')->create('portfolio_mortgage_revenue_projection_by_categories', function (Blueprint $table) {
			$table->id();
			$table->decimal('monthly_margin_rate',14,2)->default(0);
			$table->decimal('quarterly_margin_rate',14,2)->default(0);
			$table->decimal('annually_margin_rate',14,2)->default(0);
			$table->json('monthly_due_cheques_percentages')->nullable();
			$table->json('quarterly_due_cheques_percentages')->nullable();
			$table->json('annually_due_cheques_percentages')->nullable();
			$table->json('growth_rates')->nullable();
			$table->json('portfolio_mortgage_transactions_projections')->nullable();
			$table->unsignedBigInteger('study_id');
			$time = now()->toTimeString();
			$key = Str::uuid();
			$fullKey = $time.$key;
		// ref	$this->foreign('study_id','study_'.$fullKey)->references('id')->on('studies')->cascadeOnDelete();
		$table->unsignedBigInteger('company_id');
            $table->timestamps();
        });
    }

}
