<?php

use App\Providers\NonBankingServiceProvider;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSecuritizationLoanScheduleLoansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->create('securitization_loan_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('securitization_id');
            
            $table->decimal('portfolio_disbursement_amount', 14, 2)->default(0);
            // $table->json('portfolio_loan_schedule_payment_ids')->nullable();
            
            $table->decimal('portfolio_schedule_payment_sum', 14, 2)->default(0);
           
            $table->decimal('net_present_value', 14, 2)->default(0);
            $table->decimal('bank_portfolio_end_balance_sum', 14, 2);
            // $table->json('bank_portfolio_loan_schedule_payment_ids')->nullable();

            $table->decimal('securitization_profit_or_loss', 14, 2)->default(0);
            
			 $table->json('collection_revenue_amounts')->nullable();
            $table->decimal('early_settlements_expense_amount',14,2)->default(0);
            $table->decimal('securitization_expense_amount',14,2)->default(0);
            
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
