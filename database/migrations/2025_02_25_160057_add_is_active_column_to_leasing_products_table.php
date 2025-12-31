<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsActiveColumnToLeasingProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		foreach(['leasing_categories','consumerfinance_products','microfinance_products'] as $tableName){
			Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table($tableName, function (Blueprint $table) use($tableName) {
				// if(Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->hasColumn($tableName,'is_active')){
				// 	$table->dropColumn('is_active');
				// }
				$table->boolean('is_active')->default(true)->after('title');
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
        Schema::dropIfExists('microfinance_products');
    }
}
