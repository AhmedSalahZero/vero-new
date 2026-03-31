<?php

namespace App\Models;

use App\Models\QuickPricingCalculator;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string|null $name
 * @property int|null $company_id
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\QuickPricingCalculator> $quickPricingCalculators
 * @property-read int|null $quick_pricing_calculators_count
 * @property-read bool|null $quick_pricing_calculators_exists
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PricingPlan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PricingPlan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PricingPlan query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PricingPlan whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PricingPlan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PricingPlan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PricingPlan whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\PricingPlan whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PricingPlan extends Model
{
	protected $guarded = [
		'id'
	];
	
	public function getId()
	{
		return $this->id ; 
	}
    
	public function getName()
	{
		return $this->name ;
	}
	public function quickPricingCalculators()
	{
		return $this->hasMany(QuickPricingCalculator::class , 'pricing_plan_id','id');
	}
	public static function allFormattedForSelect($companyId)
    {
        $pricingPlans = PricingPlan::where('company_id',$companyId)->get();
        return formatOptionsForSelect($pricingPlans , 'getId' , 'getName');
    }
    
	
	
}
