<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperCustomizedFieldsExportation
 */
class CustomizedFieldsExportation extends Model
{

    protected $guarded = [];
    
    protected $casts = [
        'fields' => 'array',
    ];

    // Company Scoop
    public function scopeCompany($query)
    {
        return $query->where('company_id', request()->company->id);
    }
}
