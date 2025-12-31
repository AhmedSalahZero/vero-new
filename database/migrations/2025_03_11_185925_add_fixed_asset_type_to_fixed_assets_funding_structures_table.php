<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFixedAssetTypeToFixedAssetsFundingStructuresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('fixed_assets_funding_structures', function (Blueprint $table) {
            $table->string('fixed_asset_type')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('fixed_assets_funding_structures', function (Blueprint $table) {
            $table->dropColumn('fixed_asset_type');
        });
    }
}
