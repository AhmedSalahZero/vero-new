<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIjaraMortgageNewPortfolioFundingStructuresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('non_banking_service')->create('ijara_mortgage_new_portfolio_funding_structures', function (Blueprint $table) {
			$table->id();
			$table->json('equity_funding_rates')->nullable();
			$table->json('equity_funding_values')->nullable();
			$table->json('new_loans_funding_rates')->nullable();
			$table->json('new_loans_funding_values')->nullable();
			$table->unsignedBigInteger('study_id');
		//	$time = now()->toTimeString();
		//	$key = Str::uuid();
		//	$fullKey = $time.$key;
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
