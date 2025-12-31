<?php

namespace App\Models;

use App\Traits\StaticBoot;
// use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class CustomerDueCollectionAnalysis extends Model
{
    use StaticBoot;
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */

    protected $guarded = [];


    //  protected $connection= 'mysql2';
    // protected $table = 'sales_gathering';
    // protected $primaryKey  = 'user_id';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'customer_due_collection_analysis';
    public function scopeCompany($query)
    {
        return $query->where('company_id', request()->company->id?? Request('company_id') );
    }
	private static function generateSubTabArr()
	{
		return [];
	}
	public static function getTabs(int $companyId)
	{
		return [
			'exportAnalysis'=>[
				'view_name'=>__('Export Analysis'),
				'icon'=>'fa fa-crosshairs',
				'subTabs'=>[
					[
						'first_col'=>$firstColumn ='customer_name',
						'second_col'=>$secondColumn = 'product_item',
						'view_name'=>__('Customer Name Against Product Item'),
						'route'=>route('view.export.against.report',[$companyId,$firstColumn,$secondColumn])
					],[
						'first_col'=>$firstColumn ='product_item',
						'second_col'=>$secondColumn = 'customer_name',
						'view_name'=>__('Product Item Against Customer Name'),
						'route'=>route('view.export.against.report',[$companyId,$firstColumn,$secondColumn])
					],
					[
						'first_col'=>$firstColumn='shipping_line',
						'second_col'=>$secondColumn = 'destination_country',
						'view_name'=>__('Shipping Line Against Destination Country'),
						'route'=>route('view.export.against.report',[$companyId,$firstColumn,$secondColumn]),
					],
					[
						'first_col'=>$firstColumn='destination_country',
						'second_col'=>$secondColumn = 'shipping_line',
						'view_name'=>__('Destination Country Against Shipping Line'),
						'route'=>route('view.export.against.report',[$companyId,$firstColumn,$secondColumn]),
					],
					[
						'first_col'=>$firstColumn='customer_name',
						'second_col'=>$secondColumn = 'estimated_time_of_arrival',
						'view_name'=>__('Customers’ Orders Against Estimated Arrival Date'),
						'route'=>route('view.export.against.report',[$companyId,$firstColumn,$secondColumn]),
					],
					[
						'first_col'=>$firstColumn='customer_name',
						'second_col'=>$secondColumn = 'purchase_order_status',
						'view_name'=>__('Customers’ Orders Against Purchase Order Status'),
						'route'=>route('view.export.against.report',[$companyId,$firstColumn,$secondColumn]),
					],
					[
						'first_col'=>$firstColumn='purchase_order_status',
						'second_col'=>$secondColumn = 'customer_name',
						'view_name'=>__('Purchase Order Status Against Customers’ Orders'),
						'route'=>route('view.export.against.report',[$companyId,$firstColumn,$secondColumn]),
					],
					[
						'first_col'=>$firstColumn='payment_terms',
						'second_col'=>$secondColumn = 'customer_name',
						'view_name'=>__('Collection Terms Against Customers'),
						'route'=>route('view.export.against.report',[$companyId,$firstColumn,$secondColumn]),
					],[
						'first_col'=>$firstColumn='business_unit',
						'second_col'=>$secondColumn = 'revenue_stream',
						'view_name'=>__('Business Unit Against Revenue Stream'),
						'route'=>route('view.export.against.report',[$companyId,$firstColumn,$secondColumn]),
					],[
						'first_col'=>$firstColumn='export_bank',
						'second_col'=>$secondColumn = 'customer_name',
						'view_name'=>__('Export Bank Against Customer Name'),
						'route'=>route('view.export.against.report',[$companyId,$firstColumn,$secondColumn]),
					],
				]
				],
				
		];
	}
	public function getNetBalanceAttribute(){
		return $this->net_invoice_amount ;
	}
	public function getCollectedAmountAttribute()
	{
		return 0;
	}
	public function getName()
	{
		return $this->customer_name ;
	}
}
