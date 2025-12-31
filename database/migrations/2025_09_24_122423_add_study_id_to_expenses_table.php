<?php

use App\Models\NonBankingService\Study;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStudyIdToExpensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('expenses', function (Blueprint $table) {
        //     $table->unsignedBigInteger('study_id')->after('model_name')->nullable();
        // });
		// $expenses = DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('expenses')->where('model_name','Study')->get();
		
		// foreach($expenses as $expense){
		// 		DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('expenses')->where('id',$expense->id)->update([
		// 			'study_id'=>$expense->model_id
		// 		]);
				
		// }
		
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
