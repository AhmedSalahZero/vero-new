<?php

namespace App\Models;

use App\Interfaces\Models\IHaveCompany;
use App\Interfaces\Models\IHaveCreator;
use App\Models\Traits\Accessors\ServiceItemAccessor;
use App\Models\Traits\Mutators\ServiceItemMutator;
use App\Models\Traits\Relations\ServiceItemRelation;
use App\Models\Traits\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ServiceItem
 *
 * @property int $id
 * @property string $name
 * @property int $company_id
 * @property int $service_category_id
 * @property int|null $creator_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\QuotationPricingCalculator> $QuotationPricingCalculators
 * @property-read int|null $quotation_pricing_calculators_count
 * @property-read bool|null $quotation_pricing_calculators_exists
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\User|null $creator
 * @property-read \App\Models\ServiceCategory|null $serviceCategory
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ServiceItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ServiceItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ServiceItem onlyCurrentCompany(?int $companyId = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ServiceItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ServiceItem whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ServiceItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ServiceItem whereCreatorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ServiceItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ServiceItem whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ServiceItem whereServiceCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ServiceItem whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ServiceItem extends Model implements IHaveCompany,IHaveCreator
{ 
    use  ServiceItemAccessor,ServiceItemMutator , ServiceItemRelation , CompanyScope    ;
    protected $guarded = [
        'id'
    ];
	protected static function boot()
    {
        parent::boot();
        static::deleted(function ($model) {
			if($model->serviceCategory->serviceItems->count() == 0){
				$model->serviceCategory->delete();
			}
        });
    }
    

}
