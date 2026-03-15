<?php

namespace App\Models;

use App\Interfaces\Models\IHaveCompany;
use App\Interfaces\Models\IHaveCreator;
use App\Models\Traits\Accessors\ServiceCategoryAccessor;
use App\Models\Traits\Mutators\ServiceCategoryMutator;
use App\Models\Traits\Relations\ServiceCategoryRelation;
use App\Models\Traits\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ServiceCategory
 *
 * @property int $id
 * @property string $name
 * @property int $company_id
 * @property int $revenue_business_line_id
 * @property int|null $creator_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\QuotationPricingCalculator> $QuotationPricingCalculators
 * @property-read int|null $quotation_pricing_calculators_count
 * @property-read bool|null $quotation_pricing_calculators_exists
 * @property-read \App\Models\RevenueBusinessLine|null $RevenueBusinessLine
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\User|null $creator
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\QuickPricingCalculator> $quickPricingCalculator
 * @property-read int|null $quick_pricing_calculator_count
 * @property-read bool|null $quick_pricing_calculator_exists
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ServiceItem> $serviceItems
 * @property-read int|null $service_items_count
 * @property-read bool|null $service_items_exists
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ServiceCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ServiceCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ServiceCategory onlyCurrentCompany(?int $companyId = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ServiceCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ServiceCategory whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ServiceCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ServiceCategory whereCreatorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ServiceCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ServiceCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ServiceCategory whereRevenueBusinessLineId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ServiceCategory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ServiceCategory extends Model implements IHaveCompany,IHaveCreator
{ 
    use  ServiceCategoryAccessor,ServiceCategoryMutator , ServiceCategoryRelation, CompanyScope    ;
    protected $guarded = [
        'id'
    ];
	protected static function boot()
    {
        parent::boot();
        static::deleted(function ($model) {
			if($model->RevenueBusinessLine && $model->RevenueBusinessLine->serviceCategories->count() == 0){
				$model->RevenueBusinessLine->delete();
			}
        });
    }
    

}
