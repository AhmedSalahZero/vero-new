<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRenameTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('microfinance_breakdowns', function (Blueprint $table) {
			$table->renameColumn('percentage_payload','contribution_percentage');
			$table->json('flat_rates',14,2)->after('tenor')->nullable();
			$table->json('decreasing_rates',14,2)->after('tenor')->nullable();
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
