<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBalanceSheetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->create('balance_sheets', function (Blueprint $table) {
            $table->id();
			foreach([
					'monthly_non_currency_assets'=>'array',
			'total_non_currency_assets'=>'array',
			'monthly_fixed_assets'=>'array',
			'yearly_fixed_assets'=>'array',
			'monthly_other_long_term_assets'=>'array',
			'yearly_other_long_term_assets'=>'array',
			'monthly_current_assets'=>'array',
			'total_current_assets'=>'array',
			'monthly_cash_and_banks'=>'array',
			'yearly_cash_and_banks'=>'array',
			'monthly_customer_outstanding'=>'array',
			'yearly_customer_outstanding'=>'array',
			'monthly_other_debtors'=>'array',
			'yearly_other_debtors'=>'array',
			'monthly_total_assets'=>'array',
			'yearly_total_assets'=>'array',
			'monthly_current_liabilities'=>'array',
			'yearly_current_liabilities'=>'array',
			'monthly_portfolio_loan_outstanding'=>'array',
			'yearly_portfolio_loan_outstanding'=>'array',
			'monthly_other_creditors'=>'array',
			'yearly_other_creditors'=>'array',
			'monthly_long_term_liabilities'=>'array',
			'yearly_long_term_liabilities'=>'array',
			'monthly_shareholder_equity'=>'array',
			'yearly_shareholder_equity'=>'array',
			'mtls_structures'=>'array'
			] as $columnName => $castArr){
				$table->json($columnName)->nullable();
			}
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
    }
}
