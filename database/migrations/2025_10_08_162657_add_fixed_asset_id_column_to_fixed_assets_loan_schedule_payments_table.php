<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFixedAssetIdColumnToFixedAssetsLoanSchedulePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('fixed_assets_loan_schedule_payments', function (Blueprint $table) {
			$table->unsignedBigInteger('fixed_asset_id')->nullable()->after('id');
			$table->json('accured_interest')->nullable();
			$table->json('no_securitization')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fixed_assets_loan_schedule_payments', function (Blueprint $table) {
            //
        });
    }
}
