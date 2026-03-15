<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int|null $company_id
 * @property string $allocation_base
 * @property array<array-key, mixed>|null $existing_products_target
 * @property numeric|null $total_existing_target
 * @property int $use_modified_targets
 * @property array<array-key, mixed> $allocation_base_percentages
 * @property string|null $updated_by
 * @property string|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExistingProductAllocationBase company()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExistingProductAllocationBase newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExistingProductAllocationBase newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExistingProductAllocationBase query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExistingProductAllocationBase whereAllocationBase($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExistingProductAllocationBase whereAllocationBasePercentages($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExistingProductAllocationBase whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExistingProductAllocationBase whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExistingProductAllocationBase whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExistingProductAllocationBase whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExistingProductAllocationBase whereExistingProductsTarget($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExistingProductAllocationBase whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExistingProductAllocationBase whereTotalExistingTarget($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExistingProductAllocationBase whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExistingProductAllocationBase whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ExistingProductAllocationBase whereUseModifiedTargets($value)
 * @mixin \Eloquent
 */
class ExistingProductAllocationBase extends Model
{
 
    protected $table = 'existing_products_allocation_base';
   
    protected $guarded = [];

  
    protected $casts = [
        'allocation_base_percentages' => 'array',
        'existing_products_target' => 'array',
    ];
    // Company Scoop
    public function scopeCompany($query)
    {
        return $query->where('company_id', request()->company->id);
    }
}
