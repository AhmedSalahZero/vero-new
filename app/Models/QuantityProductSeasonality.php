<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int|null $company_id
 * @property string|null $name
 * @property int|null $product_id
 * @property int $category_id
 * @property numeric|null $sales_target_value
 * @property numeric|null $sales_target_quantity
 * @property string|null $seasonality
 * @property array<array-key, mixed>|null $seasonality_data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\QuantityCategory|null $category
 * @property-read \App\Models\QuantityProduct|null $product
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityProductSeasonality company()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityProductSeasonality newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityProductSeasonality newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityProductSeasonality query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityProductSeasonality whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityProductSeasonality whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityProductSeasonality whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityProductSeasonality whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityProductSeasonality whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityProductSeasonality whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityProductSeasonality whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityProductSeasonality whereSalesTargetQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityProductSeasonality whereSalesTargetValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityProductSeasonality whereSeasonality($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityProductSeasonality whereSeasonalityData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityProductSeasonality whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class QuantityProductSeasonality extends Model
{
  
    protected $table = 'quantity_products_seasonalities';
       
    protected $guarded = [];
    
    protected $casts = [
        'seasonality_data' => 'array',
    ];

    // Company Scoop
    public function scopeCompany($query)
    {
        return $query->where('company_id', request()->company->id);
    }

    /**
     * Get the user that owns the Product
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(QuantityCategory::class, 'category_id', 'id');
    }
    public function product()
    {
        return $this->belongsTo(QuantityProduct::class, 'product_id', 'id');
    }
}
