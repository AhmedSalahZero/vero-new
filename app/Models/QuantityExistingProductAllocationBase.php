<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuantityExistingProductAllocationBase extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'quantity_existing_products_allocation_base';
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
        'allocation_base_percentages' => 'array',
        'existing_products_target' => 'array',
    ];
    // Company Scoop
    public function scopeCompany($query)
    {
        return $query->where('company_id', request()->company->id);
    }
}
