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
 * @property array<array-key, mixed>|null $previous_year_seasonality
 * @property array<array-key, mixed>|null $last_3_years_seasonality
 * @property string|null $target_base
 * @property string|null $sales_target
 * @property string|null $new_start
 * @property numeric|null $growth_rate
 * @property int $add_new_products
 * @property int|null $number_of_products
 * @property string|null $seasonality
 * @property array<array-key, mixed>|null $new_seasonality
 * @property string|null $updated_by
 * @property string|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesForecast company()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesForecast newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesForecast newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesForecast query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesForecast whereAddNewProducts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesForecast whereAverageLast3Years($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesForecast whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesForecast whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesForecast whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesForecast whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesForecast whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesForecast whereGrowthRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesForecast whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesForecast whereLast3YearsSeasonality($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesForecast whereNewSeasonality($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesForecast whereNewStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesForecast whereNumberOfProducts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesForecast wherePrevious1YearSales($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesForecast wherePreviousYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesForecast wherePreviousYearGr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesForecast wherePreviousYearSeasonality($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesForecast whereSalesTarget($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesForecast whereSeasonality($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesForecast whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesForecast whereTargetBase($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesForecast whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SalesForecast whereUpdatedBy($value)
 * @mixin \Eloquent
 */
class SalesForecast extends Model
{

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
