<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPositionIdToExpensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		// DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('expenses')->delete();
        Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('expenses', function (Blueprint $table) {
			$table->dropColumn('position_id');
            $table->json('position_ids')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('expenses', function (Blueprint $table) {
            //
        });
    }
}
