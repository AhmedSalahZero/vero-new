<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWeeklyCashflowCustomDueInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('weekly_cashflow_custom_past_due_schedules', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('loan_schedule_id');
            $table->date('week_start_date');
            $table->decimal('percentage', 5)->default(100);
            $table->decimal('amount', 14, 5)->default(0);
            $table->integer('company_id');
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
        Schema::dropIfExists('weekly_cashflow_custom_due_invoices');
    }
}
