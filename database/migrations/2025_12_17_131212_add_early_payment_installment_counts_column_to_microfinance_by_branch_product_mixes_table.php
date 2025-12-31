<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEarlyPaymentInstallmentCountsColumnToMicrofinanceByBranchProductMixesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('microfinance_by_branch_product_mixes', function (Blueprint $table) {
            $table->integer('early_payment_installment_counts')->default(0)->after('avg_amount');
        }); 
		Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('microfinance_product_sales_projects', function (Blueprint $table) {
            $table->integer('early_payment_installment_counts')->default(0)->after('avg_amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('microfinance_by_branch_product_mixes', function (Blueprint $table) {
            //
        });
    }
}
