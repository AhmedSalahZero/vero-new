<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIncomeStatementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->create('income_statements', function (Blueprint $table) {
            $table->id();
			$table->json('accumulated_retained_earnings')->nullable();
			$table->json('monthly_corporate_taxes_statements')->nullable();
			$table->json('monthly_net_profit')->nullable();
			$table->unsignedBigInteger('study_id');
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
