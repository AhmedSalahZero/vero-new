<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperSecondNewProductAllocationBase
 */
class SecondNewProductAllocationBase extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'second_new_products_allocation_base';
    
    protected $guarded = [];

    
    protected $casts = [
        'allocation_base_data' => 'array',
        'new_allocation_bases_names' => 'array',
    ];
    // Company Scoop
    public function scopeCompany($query)
    {
        return $query->where('company_id', request()->company->id);
    }
}
