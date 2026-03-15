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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\AllocationSetting company()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\AllocationSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\AllocationSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\AllocationSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\AllocationSetting whereAddNewItems($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\AllocationSetting whereAllocationBase($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\AllocationSetting whereBreakdown($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\AllocationSetting whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\AllocationSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\AllocationSetting whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\AllocationSetting whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\AllocationSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\AllocationSetting whereNumberOfItems($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\AllocationSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\AllocationSetting whereUpdatedBy($value)
 * @mixin \Eloquent
 */
class AllocationSetting extends Model
{

    protected $table = 'allocation_settings';
   
    protected $guarded = [];
    // Company Scoop
    public function scopeCompany($query)
    {
        return $query->where('company_id', request()->company->id);
    }
}
