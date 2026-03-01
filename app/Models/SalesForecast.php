<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperSalesForecast
 */
class SalesForecast extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sales_forecast';
    
    protected $guarded = [];
    
    protected $casts = [
        'previous_year_seasonality' => 'array',
        'last_3_years_seasonality' => 'array',
        'new_seasonality' => 'array',
    ];

    // Company Scoop
    public function scopeCompany($query)
    {
        return $query->where('company_id', request()->company->id);
    }
    public static function boot() {
        parent::boot();

        static::deleting(function($forecast) { // before delete() method call this
            $categories = Category::company()->get();
            count($categories) == 0 ?: $categories->each->delete();
            $seasonalities = ProductSeasonality::company()->get();
            count($seasonalities) == 0 ?: $seasonalities->each->delete();
            $targets = ModifiedTarget::company()->first();
            $targets === null ?: $targets->delete();
        });
    }
}
