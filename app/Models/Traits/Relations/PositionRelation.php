<?php
namespace App\Models\Traits\Relations ;

use App\Models\QuickPricingCalculator;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait PositionRelation
{
    
     public function freelancerExpenseAble():MorphToMany
    {
        // position_id [foreign key on the pivot table to identify (this model) record]
        return $this->morphedByMany(QuickPricingCalculator::class ,'freelancerExpenseAble','freelancer_expense_quick_pricing_calculator','position_id');
    }
    public function directManpowerExpenseAble():MorphToMany
    {
        // position_id [foreign key on the pivot table to identify (this model) record]
        return $this->morphedByMany(QuickPricingCalculator::class ,'directManpowerExpenseAble','direct_manpower_expense_quick_pricing_calculator','position_id');
    }
	public function creator():BelongsTo
	{
		return $this->belongsTo(User::class , 'creator_id','id');
	}
    
           
}
