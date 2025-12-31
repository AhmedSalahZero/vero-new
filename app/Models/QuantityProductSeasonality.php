<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuantityProductSeasonality extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'quantity_products_seasonalities';
       /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
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
