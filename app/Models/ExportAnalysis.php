<?php

namespace App\Models;

use App\Traits\StaticBoot;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $company_id
 * @property string|null $revenue_stream
 * @property string|null $purchase_order_number
 * @property string|null $purchase_order_date
 * @property string|null $business_unit
 * @property string|null $customer_name
 * @property string|null $consignee
 * @property string|null $loading_country
 * @property string|null $destination_country
 * @property string|null $broker
 * @property string|null $category
 * @property string|null $sub_category
 * @property string|null $product_item
 * @property string|null $origin
 * @property string|null $packing_unit_of_measurement
 * @property string|null $packing_quantity
 * @property string|null $packing_type
 * @property string|null $full_container_load_count
 * @property string|null $full_container_load_type
 * @property string|null $quantity_unit_of_measurement
 * @property string|null $quantity
 * @property string|null $incoterm
 * @property string|null $currency
 * @property string|null $price_per_unit
 * @property string|null $purchase_order_value
 * @property string|null $freight_value
 * @property string|null $purchase_order_net_value
 * @property string|null $payment_terms
 * @property string|null $shipping_line
 * @property string|null $booking_number
 * @property string|null $port_of_loading
 * @property string|null $port_of_destination
 * @property string|null $cut_off_date
 * @property string|null $estimated_time_of_sailing
 * @property string|null $estimated_time_of_arrival
 * @property string|null $inspection_company
 * @property string|null $clearance_agent
 * @property string|null $export_bank
 * @property string|null $documents_sending_type
 * @property string|null $purchase_order_status
 * @property \Illuminate\Support\Carbon $created_at
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExportAnalysis company()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExportAnalysis newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExportAnalysis newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExportAnalysis query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExportAnalysis whereBookingNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExportAnalysis whereBroker($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExportAnalysis whereBusinessUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExportAnalysis whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExportAnalysis whereClearanceAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExportAnalysis whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExportAnalysis whereConsignee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExportAnalysis whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExportAnalysis whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExportAnalysis whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExportAnalysis whereCustomerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExportAnalysis whereCutOffDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExportAnalysis whereDestinationCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExportAnalysis whereDocumentsSendingType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExportAnalysis whereEstimatedTimeOfArrival($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExportAnalysis whereEstimatedTimeOfSailing($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExportAnalysis whereExportBank($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExportAnalysis whereFreightValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExportAnalysis whereFullContainerLoadCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExportAnalysis whereFullContainerLoadType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExportAnalysis whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExportAnalysis whereIncoterm($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExportAnalysis whereInspectionCompany($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExportAnalysis whereLoadingCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExportAnalysis whereOrigin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExportAnalysis wherePackingQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExportAnalysis wherePackingType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExportAnalysis wherePackingUnitOfMeasurement($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExportAnalysis wherePaymentTerms($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExportAnalysis wherePortOfDestination($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExportAnalysis wherePortOfLoading($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExportAnalysis wherePricePerUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExportAnalysis whereProductItem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExportAnalysis wherePurchaseOrderDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExportAnalysis wherePurchaseOrderNetValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExportAnalysis wherePurchaseOrderNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExportAnalysis wherePurchaseOrderStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExportAnalysis wherePurchaseOrderValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExportAnalysis whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExportAnalysis whereQuantityUnitOfMeasurement($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExportAnalysis whereRevenueStream($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExportAnalysis whereShippingLine($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExportAnalysis whereSubCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExportAnalysis whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ExportAnalysis extends Model
{
    use StaticBoot;
    

    protected $guarded = [];


    //  protected $connection= 'mysql2';
    // protected $table = 'sales_gathering';
    // protected $primaryKey  = 'user_id';


    protected $table = 'export_analysis';
    public function scopeCompany($query)
    {
        return $query->where('company_id', request()->company->id?? Request('company_id') );
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
					],
					[
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
	public function getDeleteByDateColumnName()
	{
		return 'purchase_order_date';
	}
}
