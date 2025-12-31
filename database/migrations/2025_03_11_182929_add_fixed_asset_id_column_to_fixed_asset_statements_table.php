<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFixedAssetIdColumnToFixedAssetStatementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('fixed_asset_statements', function (Blueprint $table) {
			if(Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->hasColumn('fixed_asset_statements','fixed_asset_id')){
				$table->unsignedInteger('fixed_asset_id')->change();
			}else{
				$table->unsignedInteger('fixed_asset_id');
			}
			$table->foreign('fixed_asset_id')->references('id')->on('fixed_assets')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('fixed_asset_statements', function (Blueprint $table) {
           $table->dropForeign('fixed_asset_statements_fixed_asset_id_foreign');
		   $table->dropColumn('fixed_asset_id');
        });
    }
}
