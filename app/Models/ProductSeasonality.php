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
 * @property numeric|null $sales_target_percentage
 * @property string|null $seasonality
 * @property array<array-key, mixed>|null $seasonality_data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\Category|null $category
 * @property-read \App\Models\Product|null $product
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ProductSeasonality company()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ProductSeasonality newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ProductSeasonality newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ProductSeasonality query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ProductSeasonality whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ProductSeasonality whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ProductSeasonality whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ProductSeasonality whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ProductSeasonality whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ProductSeasonality whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ProductSeasonality whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ProductSeasonality whereSalesTargetPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ProductSeasonality whereSalesTargetValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ProductSeasonality whereSeasonality($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ProductSeasonality whereSeasonalityData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ProductSeasonality whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ProductSeasonality extends Model
{
 
    protected $table = 'products_seasonalities';
       
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
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
