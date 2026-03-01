<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperQuantityProductSeasonality
 */
class QuantityProductSeasonality extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
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
