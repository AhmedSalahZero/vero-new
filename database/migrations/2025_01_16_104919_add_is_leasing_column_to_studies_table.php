<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsLeasingColumnToStudiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('non_banking_service')->table('studies', function (Blueprint $table) {
            $table->boolean('has_leasing')->default(0);
            $table->boolean('has_direct_factoring')->default(0);
            $table->boolean('has_reverse_factoring')->default(0);
            $table->boolean('has_ijara_mortgage')->default(0);
            $table->boolean('has_portfolio_mortgage')->default(0);
            $table->boolean('has_micro_finance')->default(0);
            $table->boolean('has_securitization')->default(0);
            $table->boolean('has_consumer_finance')->default(0);
			
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('studies', function (Blueprint $table) {
            //
        });
    }
}
