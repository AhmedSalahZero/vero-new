<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesGatheringProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		foreach(['product'=>'sales_gathering_products','sales_channel'=>'sales_gathering_sales_channels','branch'=>'sales_gathering_branches','principle'=>'sales_gathering_principles'] as $id=>$tableName){
			Schema::create($tableName, function (Blueprint $table) use ($id) {
				$table->id();
				$table->string('name');
				$table->boolean('is_existing')->default(1)->comment('is new '. $id .' in financial planning study');
				$table->boolean('is_new')->default(0)->comment('is new '.$id .' in financial planning study');
				$table->unsignedBigInteger('company_id');
				$table->unsignedBigInteger('study_id')->nullable();
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
        Schema::dropIfExists('sales_gathering_products');
    }
}
