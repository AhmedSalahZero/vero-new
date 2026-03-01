<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperModifiedSeasonality
 */
class ModifiedSeasonality extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'modified_seasonality';
       
    protected $guarded = [];
    
    protected $casts = [
        'original_seasonality' => 'array',
        'modified_seasonality' => 'array',
    ];

    // Company Scoop
    public function scopeCompany($query)
    {
        return $query->where('company_id', request()->company->id);
    }
}
