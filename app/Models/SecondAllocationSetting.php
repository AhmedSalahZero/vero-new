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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SecondAllocationSetting company()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SecondAllocationSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SecondAllocationSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SecondAllocationSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SecondAllocationSetting whereAddNewItems($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SecondAllocationSetting whereAllocationBase($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SecondAllocationSetting whereBreakdown($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SecondAllocationSetting whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SecondAllocationSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SecondAllocationSetting whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SecondAllocationSetting whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SecondAllocationSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SecondAllocationSetting whereNumberOfItems($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SecondAllocationSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SecondAllocationSetting whereUpdatedBy($value)
 * @mixin \Eloquent
 */
class SecondAllocationSetting extends Model
{
  
    protected $table = 'second_allocation_settings';
    
    protected $guarded = [];
    // Company Scoop
    public function scopeCompany($query)
    {
        return $query->where('company_id', request()->company->id);
    }
}
