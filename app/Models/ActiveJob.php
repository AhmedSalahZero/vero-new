<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActiveJob extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'active_jobs';
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
    // Company Scoop
    public function scopeCompany($query)
    {
        return $query->where('company_id', request()->company->id);
    }
}
