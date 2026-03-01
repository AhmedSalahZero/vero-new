<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperExistingProductAllocationBase
 */
class ExistingProductAllocationBase extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'existing_products_allocation_base';
   
    protected $guarded = [];

  
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
