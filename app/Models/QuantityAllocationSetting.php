<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int|null $company_id
 * @property string $allocation_base
 * @property string|null $breakdown
 * @property int $add_new_items
 * @property int $number_of_items
 * @property string|null $updated_by
 * @property string|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityAllocationSetting company()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityAllocationSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityAllocationSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityAllocationSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityAllocationSetting whereAddNewItems($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityAllocationSetting whereAllocationBase($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityAllocationSetting whereBreakdown($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityAllocationSetting whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityAllocationSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityAllocationSetting whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityAllocationSetting whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityAllocationSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityAllocationSetting whereNumberOfItems($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityAllocationSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityAllocationSetting whereUpdatedBy($value)
 * @mixin \Eloquent
 */
class QuantityAllocationSetting extends Model
{
   
    protected $table = 'quantity_allocation_settings';
    
    protected $guarded = [];
    // Company Scoop
    public function scopeCompany($query)
    {
        return $query->where('company_id', request()->company->id);
    }
}
