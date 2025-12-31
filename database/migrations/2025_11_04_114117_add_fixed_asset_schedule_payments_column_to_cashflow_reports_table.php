<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFixedAssetSchedulePaymentsColumnToCashflowReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('cashflow_statement_reports', function (Blueprint $table) {
            $table->json('opening_cash')->after('id')->nullable();
            $table->json('existing_portfolio_collection')->after('microfinance_payment')->nullable();
            $table->json('existing_other_debtors_collection')->after('existing_portfolio_collection')->nullable();
            $table->json('existing_portfolio_loans_payment')->after('existing_other_debtors_collection')->nullable();
            $table->json('existing_other_creditors_payment')->after('existing_portfolio_loans_payment')->nullable();
            $table->json('existing_long_term_loans_payment')->after('existing_other_creditors_payment')->nullable();
            $table->json('existing_other_long_term_loans_payment')->after('existing_long_term_loans_payment')->nullable();
            $table->json('fixed_asset_loan_schedule_payments')->after('existing_other_long_term_loans_payment')->nullable();
            $table->json('fixed_asset_payments')->after('fixed_asset_loan_schedule_payments')->nullable();
            $table->json('expense_payments')->after('fixed_asset_payments')->nullable();
            $table->json('salary_payments')->after('expense_payments')->nullable();
            $table->json('salary_tax_social_insurance_payments')->after('salary_payments')->nullable();
            $table->json('withhold_payments')->after('salary_tax_social_insurance_payments')->nullable();
            $table->json('corporate_taxes_payments')->after('withhold_payments')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cashflow_reports', function (Blueprint $table) {
            //
        });
    }
}
