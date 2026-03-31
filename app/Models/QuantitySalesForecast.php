<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int|null $company_id
 * @property string|null $start_date
 * @property string|null $end_date
 * @property int|null $previous_year
 * @property string|null $previous_1_year_sales
 * @property numeric|null $previous_year_gr
 * @property string|null $average_last_3_years
 * @property array<array-key, mixed>|null $others_products_previous_year
 * @property array<array-key, mixed>|null $others_products_previous_3_year
 * @property array<array-key, mixed>|null $previous_year_seasonality
 * @property array<array-key, mixed>|null $last_3_years_seasonality
 * @property array<array-key, mixed>|null $forecasted_sales
 * @property string|null $target_base
 * @property string|null $sales_target
 * @property numeric|null $prices_increase_rate
 * @property numeric|null $other_products_growth_rate
 * @property numeric|null $quantity_growth_rate
 * @property int $add_new_products
 * @property int|null $number_of_products
 * @property string|null $seasonality
 * @property string|null $new_seasonality
 * @property string|null $updated_by
 * @property string|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantitySalesForecast company()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantitySalesForecast newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantitySalesForecast newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantitySalesForecast query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantitySalesForecast whereAddNewProducts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantitySalesForecast whereAverageLast3Years($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantitySalesForecast whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantitySalesForecast whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantitySalesForecast whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantitySalesForecast whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantitySalesForecast whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantitySalesForecast whereForecastedSales($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantitySalesForecast whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantitySalesForecast whereLast3YearsSeasonality($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantitySalesForecast whereNewSeasonality($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantitySalesForecast whereNumberOfProducts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantitySalesForecast whereOtherProductsGrowthRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantitySalesForecast whereOthersProductsPrevious3Year($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantitySalesForecast whereOthersProductsPreviousYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantitySalesForecast wherePrevious1YearSales($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantitySalesForecast wherePreviousYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantitySalesForecast wherePreviousYearGr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantitySalesForecast wherePreviousYearSeasonality($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantitySalesForecast wherePricesIncreaseRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantitySalesForecast whereQuantityGrowthRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantitySalesForecast whereSalesTarget($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantitySalesForecast whereSeasonality($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantitySalesForecast whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantitySalesForecast whereTargetBase($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantitySalesForecast whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantitySalesForecast whereUpdatedBy($value)
 * @mixin \Eloquent
 */
class QuantitySalesForecast extends Model
{
   
    protected $table = 'quantity_sales_forecast';
    
    protected $guarded = [];
    
    protected $casts = [
        'previous_year_seasonality' => 'array',
        'last_3_years_seasonality' => 'array',
        'others_products_previous_year' => 'array',
        'others_products_previous_3_year' => 'array',
        'forecasted_sales' => 'array',
    ];

    // Company Scoop
    public function scopeCompany($query)
    {
        return $query->where('company_id', request()->company->id);
    }
    public static function boot() {
        parent::boot();

        static::deleting(function($forecast) { // before delete() method call this
            $categories = QuantityCategory::company()->get();
            count($categories) == 0 ?: $categories->each->delete();
            $seasonalities = QuantityProductSeasonality::company()->get();
            count($seasonalities) == 0 ?: $seasonalities->each->delete();
            $targets = QuantityModifiedTarget::company()->first();
            $targets === null ?: $targets->delete();
        });
    }
}
