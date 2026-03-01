<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\StaticBoot;
/**
 * @mixin IdeHelperInventoryStatement
 */
class InventoryStatement extends Model
{
    use StaticBoot;
    // SoftDeletes

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
