<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePositionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::connection(FINANCIAL_PLANNING_CONNECTION_NAME)->dropIfExists('positions');
        Schema::connection(FINANCIAL_PLANNING_CONNECTION_NAME)->create('positions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
			$table->unsignedBigInteger('department_id');
		//	$table->foreign('department_id')->references('id')->on('financial_planning.departments')->cascadeOnDelete();
			$table->unsignedInteger('existing_count')->default(0);
			$table->decimal('monthly_net_salary',14,2)->default(0);
            $table->json('payload')->nullable();
			$table->studyFields();
            // $table->integer('company_id');
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
        Schema::dropIfExists('expenses');
    }
}
