<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModifiedSeasonality extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'modified_seasonality';
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
        'original_seasonality' => 'array',
        'modified_seasonality' => 'array',
    ];

    // Company Scoop
    public function scopeCompany($query)
    {
        return $query->where('company_id', request()->company->id);
    }
}
