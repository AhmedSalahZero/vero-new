<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMinIndexToSalesGatheringTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales_gathering', function (Blueprint $table) {
            $table->dropIndex('min__index');
            $table->index(['customer_name', 'Year', 'Month', 'company_id', 'net_sales_value'], 'min__index');
			
			
			$indexes = [
                'min__index_country' => ['company_id', 'customer_name', 'country', 'net_sales_value', 'Year'],
                'min__index_zone' => ['company_id', 'customer_name', 'zone', 'net_sales_value', 'Year'],
                'min__index_branch' => ['company_id', 'customer_name', 'branch', 'net_sales_value', 'Year'],
                'min__index_sales_person' => ['company_id', 'customer_name', 'sales_person', 'net_sales_value', 'Year'],
                'min__index_business_sector' => ['company_id', 'customer_name', 'business_sector', 'net_sales_value', 'Year'],
                'min__index_sales_channel' => ['company_id', 'customer_name', 'sales_channel', 'net_sales_value', 'Year'],
                'min__index_category' => ['company_id', 'customer_name', 'category', 'net_sales_value', 'Year'],
                'min__index_product_or_service' => ['company_id', 'customer_name', 'product_or_service', 'net_sales_value', 'Year'],
                'min__index_product_item' => ['company_id', 'customer_name', 'product_item', 'net_sales_value', 'Year'],
            ];

            // Drop each index and recreate it with the Month column
            foreach ($indexes as $indexName => $columns) {
                // Drop the existing index
                $table->dropIndex($indexName);

                // Create the new index with Month added (after Year for time-based queries)
                $newColumns = array_merge(array_slice($columns, 0, 2), ['Month'], array_slice($columns, 2));
                $table->index($newColumns, $indexName);
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
       
    }
}
