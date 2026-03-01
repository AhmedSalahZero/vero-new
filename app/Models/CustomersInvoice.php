<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\StaticBoot;
/**
 * @mixin IdeHelperCustomersInvoice
 */
class CustomersInvoice extends Model
{
    use SoftDeletes,StaticBoot;
    protected $guarded = [];
    public function scopeCompany($query)
    {
        return $query->where('company_id', request()->company->id);
    }

}
