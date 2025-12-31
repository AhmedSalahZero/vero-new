<?php

use App\View\Components\Table;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeFullDateTimeToDateToCleanOverdraftsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		$tableName = 'clean_overdrafts';
		
        Schema::table($tableName, function (Blueprint $table) {
			$table->renameColumn('oldest_full_date','oldest_date');
        });
		Schema::table($tableName, function (Blueprint $table) {
			$table->date('oldest_date')->nullable()->change();
        });
		$cleanOverdrafts = DB::table($tableName)->get();
		foreach($cleanOverdrafts as $cleanOverdraft){
			$id = $cleanOverdraft->id ;
			$oldestDate = $cleanOverdraft->oldest_date ; 
			if($oldestDate){
				Carbon::make($oldestDate)->format('Y-m-d');
			}
			DB::table($tableName)->where('id',$id)->update([
				'oldest_date'=>$oldestDate
			]);
		}
		
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
