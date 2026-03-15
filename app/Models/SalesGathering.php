<?php

namespace App\Models;

use App\Traits\StaticBoot;
// use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int|null $company_id
 * @property string|null $date
 * @property string|null $day_name
 * @property string|null $country
 * @property string|null $local_or_export
 * @property string|null $branch
 * @property string|null $document_type
 * @property string|null $document_number
 * @property string|null $sales_person
 * @property string|null $business_unit
 * @property string|null $customer_name
 * @property string|null $business_sector
 * @property string|null $zone
 * @property string|null $sales_channel
 * @property string|null $service_provider_type
 * @property string|null $service_provider_name
 * @property int|null $service_provider_birth_year
 * @property string|null $principle
 * @property string|null $category
 * @property string|null $sub_category
 * @property string|null $product_or_service
 * @property string|null $product_item
 * @property string|null $measurment_unit
 * @property string|null $return_reason
 * @property numeric|null $quantity
 * @property string|null $quantity_status
 * @property numeric|null $quantity_bonus
 * @property numeric|null $price_per_unit
 * @property numeric|null $sales_value
 * @property numeric|null $quantity_discount
 * @property numeric|null $cash_discount
 * @property numeric|null $special_discount
 * @property numeric|null $other_discounts
 * @property numeric|null $net_sales_value
 * @property string|null $updated_by
 * @property string|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property string|null $Day
 * @property string|null $Month
 * @property string|null $Year
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering company($request = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering whereBranch($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering whereBusinessSector($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering whereBusinessUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering whereCashDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering whereCustomerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering whereDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering whereDayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering whereDocumentNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering whereDocumentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering whereLocalOrExport($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering whereMeasurmentUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering whereMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering whereNetSalesValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering whereOtherDiscounts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering wherePricePerUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering wherePrinciple($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering whereProductItem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering whereProductOrService($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering whereQuantityBonus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering whereQuantityDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering whereQuantityStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering whereReturnReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering whereSalesChannel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering whereSalesPerson($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering whereSalesValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering whereServiceProviderBirthYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering whereServiceProviderName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering whereServiceProviderType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering whereSpecialDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering whereSubCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering whereYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGathering whereZone($value)
 * @mixin \Eloquent
 */
class SalesGathering extends Model
{
    use StaticBoot;
    

    protected $guarded = [];


    protected $table = 'sales_gathering';
    public function scopeCompany($query,$request = null )
    {
        return $query->where('company_id', request()->company->id?? Request('company_id',$request? $request->get('company_id'):null) );
    }
	public static function getTrendAnalysisTabs(int $companyId)
	{
		$trendTabs = TablesField::where('is_sales_trend',1)->pluck('field_name','view_name')->toArray();

		
		return [
			$mainReportName = 'zone'=>[
				'view_name'=>__('Zones'),
				'show'=>hasExport([$mainReportName],$companyId),
				'icon'=>'fa fa-crosshairs',
				'subTabs'=>[
					[
						'first_col'=>$mainReportName,
						'show'=>true ,
						'view_name'=>__('Zones Sales Analysis'),
						'route'=>route('view.single.trend.analysis',[$companyId,$mainReportName])
					],
					[
						'first_col'=>$mainReportName,
						'second_col'=>$secondColumn = 'sales_channel',
						'view_name'=>__('Zone Against Sales Channels'),
						'route'=>route('view.against.trend.analysis',[$companyId,$mainReportName,$secondColumn])
					],
					[
						'first_col'=>$mainReportName,
						'second_col'=>$secondColumn = 'sales_channel',
						'view_name'=>__('Zone Against Sales Channels'),
						'route'=>route('view.against.trend.analysis',[$companyId,$mainReportName,$secondColumn])
					],
				]
				],
				
		];
	}
	public function getDeleteByDateColumnName()
	{
		return 'date';
	}
	
}
