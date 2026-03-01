<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperQuantityModifiedTarget
 */
class QuantityModifiedTarget extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'quantity_modified_targe';
       
    protected $guarded = [];
    
    protected $casts = [
        'products_modified_targets' => 'array',
        'others_target' => 'array',
        'sales_targets_percentages' => 'array',
    ];

    // Company Scoop
    public function scopeCompany($query)
    {
        return $query->where('company_id', request()->company->id);
    }

}
