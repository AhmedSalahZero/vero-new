<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperCollectionSetting
 */
class CollectionSetting extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'collection_settings';
   
    protected $guarded = [];
   
    protected $casts = [
        'general_collection' => 'array',
        'first_allocation_collection' => 'array',
        'second_allocation_collection' => 'array',
    ];
    // Company Scoop
    public function scopeCompany($query)
    {
        return $query->where('company_id', request()->company->id);
    }
}
