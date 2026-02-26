<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\StaticBoot;
class CustomersInvoice extends Model
{
    use SoftDeletes,StaticBoot;
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */

    protected $guarded = [];
    /**
     * Get the
     *
     * @param  string  $value
     * @return string
     */


    public function scopeCompany($query)
    {
        return $query->where('company_id', request()->company->id);
    }

}
