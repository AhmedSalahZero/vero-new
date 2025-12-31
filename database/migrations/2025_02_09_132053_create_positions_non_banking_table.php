<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePositionsNonBankingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->create('microfinance_positions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
			$table->unsignedBigInteger('department_id');
			$table->string('expense_type')->comment('cost-of-service for example')->nullable();
			$table->unsignedInteger('existing_count')->default(0);
			$table->decimal('monthly_net_salary',14,2)->default(0);
            $table->json('payload')->nullable();
			$table->studyFields();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->nullable();
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
