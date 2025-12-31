<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
class CreateLoanSchedulePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('non_banking_service')->create('loan_schedule_payments', function (Blueprint $table) {
            $table->id();
			$table->string('revenue_stream_type')->comment('leasing or factoring or ...')->nullable();
			$table->string('portfolio_loan_type')->nullable()->comment('bank_portfolio or portfolio');
			$table->unsignedBigInteger('revenue_stream_id')->comment('LeasingRevenueStreamBreakdown')->nullable();
			$table->unsignedBigInteger('revenue_stream_category_id')->comment('LeasingCategory')->nullable();
			$table->integer('month_as_index');
			$table->string('loan_type')->nullable();
			$table->json('totals');
			$table->json('beginning');
			$table->json('interestAmount');
			$table->json('schedulePayment');
			$table->json('principleAmount');
			$table->json('endBalance');
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
        Schema::dropIfExists('loan_schedule_payments');
    }
};
