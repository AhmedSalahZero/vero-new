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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantitySecondAllocationSetting company()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantitySecondAllocationSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantitySecondAllocationSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantitySecondAllocationSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantitySecondAllocationSetting whereAddNewItems($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantitySecondAllocationSetting whereAllocationBase($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantitySecondAllocationSetting whereBreakdown($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantitySecondAllocationSetting whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantitySecondAllocationSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantitySecondAllocationSetting whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantitySecondAllocationSetting whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantitySecondAllocationSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantitySecondAllocationSetting whereNumberOfItems($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantitySecondAllocationSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantitySecondAllocationSetting whereUpdatedBy($value)
 * @mixin \Eloquent
 */
class QuantitySecondAllocationSetting extends Model
{
   
    protected $table = 'quantity_second_allocation_settings';
    
    protected $guarded = [];
    // Company Scoop
    public function scopeCompany($query)
    {
        return $query->where('company_id', request()->company->id);
    }
}
