<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpenseNamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->create('expense_names', function (Blueprint $table) {
            $table->id();
			$table->unsignedBigInteger('company_id');
			$table->string('expense_type')->nullable();
			$table->string('name');
			$table->boolean('is_employee_expense')->default(false);
			$table->boolean('is_branch_expense')->default(false);
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
        Schema::dropIfExists('expense_names');
    }
}
