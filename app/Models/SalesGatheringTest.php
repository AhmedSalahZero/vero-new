<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
// use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\StaticBoot;
/**
 * @property int $id
 * @property int|null $company_id
 * @property string|null $date
 * @property string|null $country
 * @property string|null $local_or_export
 * @property string|null $branch
 * @property string|null $document_type
 * @property string|null $document_number
 * @property string|null $sales_person
 * @property string|null $customer_code
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
 * @property array<array-key, mixed>|null $validation
 * @property string|null $updated_by
 * @property string|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGatheringTest company()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGatheringTest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGatheringTest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGatheringTest query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGatheringTest whereBranch($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGatheringTest whereBusinessSector($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGatheringTest whereCashDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGatheringTest whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGatheringTest whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGatheringTest whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGatheringTest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGatheringTest whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGatheringTest whereCustomerCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGatheringTest whereCustomerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGatheringTest whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGatheringTest whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGatheringTest whereDocumentNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGatheringTest whereDocumentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGatheringTest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGatheringTest whereLocalOrExport($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGatheringTest whereMeasurmentUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGatheringTest whereNetSalesValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGatheringTest whereOtherDiscounts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGatheringTest wherePricePerUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGatheringTest wherePrinciple($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGatheringTest whereProductItem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGatheringTest whereProductOrService($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGatheringTest whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGatheringTest whereQuantityBonus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGatheringTest whereQuantityDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGatheringTest whereQuantityStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGatheringTest whereReturnReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGatheringTest whereSalesChannel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGatheringTest whereSalesPerson($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGatheringTest whereSalesValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGatheringTest whereServiceProviderBirthYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGatheringTest whereServiceProviderName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGatheringTest whereServiceProviderType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGatheringTest whereSpecialDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGatheringTest whereSubCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGatheringTest whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGatheringTest whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGatheringTest whereValidation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesGatheringTest whereZone($value)
 * @mixin \Eloquent
 */
class SalesGatheringTest extends Model
{
     use StaticBoot;
    
    protected $guarded = [];

  
    protected $table = 'sales_gathering_tests';

    /**
     * Scope a query to only include
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCompany($query)
    {
        return $query->where('company_id', request()->company->id);
    }
    /**
     * Scope a query to only include
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    // public function scopeWithoutValidation($query)
    // {
    //     return $query->except(['validation']);
    // }
    
    protected $casts = [
        'validation' => 'array',
    ];
}
