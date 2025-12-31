<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShareholderEquityMultiplierToStudiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(FINANCIAL_PLANNING_CONNECTION_NAME)->table('studies', function (Blueprint $table) {
			
			
			
			
			
			
			if(Schema::connection(FINANCIAL_PLANNING_CONNECTION_NAME)->hasColumn('studies','investment_return_rate')){
				if(Schema::connection(FINANCIAL_PLANNING_CONNECTION_NAME)->hasColumn('studies','revenue_multiplier')){
					$table->removeColumn('investment_return_rate');
				}
				else{
					$table->renameColumn('investment_return_rate','revenue_multiplier');
				}
			}
			
			if(!Schema::connection(FINANCIAL_PLANNING_CONNECTION_NAME)->hasColumn('studies','shareholder_equity_multiplier')){
				$table->decimal('shareholder_equity_multiplier',14,2)->default(0);
			}
			$table->removeColumn('perpetual_growth_rate');
            if(!Schema::connection(FINANCIAL_PLANNING_CONNECTION_NAME)->hasColumn('studies','has_trading')){
			
				$table->boolean('has_trading')->default(0);
			}
            if(!Schema::connection(FINANCIAL_PLANNING_CONNECTION_NAME)->hasColumn('studies','has_manufacturing')){
			
				$table->boolean('has_manufacturing')->default(0);
			}
			if(!Schema::connection(FINANCIAL_PLANNING_CONNECTION_NAME)->hasColumn('studies','has_service')){
				$table->boolean('has_service')->default(0);
			
			}if(!Schema::connection(FINANCIAL_PLANNING_CONNECTION_NAME)->hasColumn('studies','has_service_with_inventory')){
				$table->boolean('has_service_with_inventory')->default(0);
			
			}
			
			if(!Schema::connection(FINANCIAL_PLANNING_CONNECTION_NAME)->hasColumn('studies','main_planning_base')){
				$table->boolean('main_planning_base')->default(0);
			
			}
			if(!Schema::connection(FINANCIAL_PLANNING_CONNECTION_NAME)->hasColumn('studies','sub_planning_base')){
				$table->boolean('sub_planning_base')->default(0);
			
			}
			if(!Schema::connection(FINANCIAL_PLANNING_CONNECTION_NAME)->hasColumn('studies','add_new_from_main_planning')){
				$table->string('add_new_from_main_planning')->comment('product_or_service , sales_channel , etc')->nullable();
			}
			if(!Schema::connection(FINANCIAL_PLANNING_CONNECTION_NAME)->hasColumn('studies','add_new_from_sub_planning')){
				$table->string('add_new_from_sub_planning')->comment('product_or_service , sales_channel , etc')->nullable();

			}
          
			
			
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
