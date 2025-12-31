<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerOpeningBalancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		foreach(['customer_opening_balances','supplier_opening_balances'] as $tableName){
			Schema::create($tableName, function (Blueprint $table) {
            $table->id();
			$table->date('date');
			$table->unsignedBigInteger('company_id');
            $table->timestamps();
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
        Schema::dropIfExists('customer_opening_balances');
    }
}
