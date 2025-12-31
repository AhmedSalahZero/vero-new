<?php

use App\Console\Commands\TestCommand;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EditAccountNumberColumnToBeString extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		$tables = (new TestCommand)->getTableNamesThatHasColumn('account_number');
		foreach($tables as $tableName){
			Schema::table($tableName, function (Blueprint $table) {
				$table->string('account_number')->nullable()->change();
			});
		}
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('be_string', function (Blueprint $table) {
            //
        });
    }
}
