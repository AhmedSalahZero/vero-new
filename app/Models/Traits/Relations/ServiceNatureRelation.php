<?php
namespace App\Models\Traits\Relations ;

use App\Models\QuickPricingCalculator;
use App\Models\QuotationPricingCalculator;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait ServiceNatureRelation
{
    public function QuotationPricingCalculators():MorphToMany
   {
       return $this->morphedByMany(QuotationPricingCalculator::class , 'serviceable');
   }
   
    public function quickPricingCalculator():HasMany
    {
        return $this->hasMany(QuickPricingCalculator::class ,'service_nature_id','id');
    }
	public function creator():BelongsTo
	{
		return $this->belongsTo(User::class , 'creator_id','id');
	}   
}
