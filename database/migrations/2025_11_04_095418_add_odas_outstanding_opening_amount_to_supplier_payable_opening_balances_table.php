<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOdasOutstandingOpeningAmountToSupplierPayableOpeningBalancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('supplier_payable_opening_balances', function (Blueprint $table) {
            $table->decimal('odas_outstanding_opening_amount',14,5)->default(0);
            $table->json('portfolio_interest_expenses')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('supplier_payable_opening_balances', function (Blueprint $table) {
            //
        });
    }
}
