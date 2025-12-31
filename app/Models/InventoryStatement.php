<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\StaticBoot;
class InventoryStatement extends Model
{
    use StaticBoot;
    // SoftDeletes
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */

    protected $guarded = [];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'inventory_statements';
    public function scopeCompany($query)
    {

        return $query->where('company_id', request()->company->id);
    }
}
