<?php
namespace App\Models\Traits\Relations ;

use App\Models\QuickPricingCalculator;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait CurrencyRelation
{
    public function quickPricingCalculators():HasMany
    {
        return $this->hasMany(QuickPricingCalculator::class ,'currency_id','id');
    }
    public function creator():BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id', 'id');
    }
}
