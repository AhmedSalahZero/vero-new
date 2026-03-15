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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SecondExistingProductAllocationBase company()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SecondExistingProductAllocationBase newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SecondExistingProductAllocationBase newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SecondExistingProductAllocationBase query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SecondExistingProductAllocationBase whereAllocationBase($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SecondExistingProductAllocationBase whereAllocationBasePercentages($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SecondExistingProductAllocationBase whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SecondExistingProductAllocationBase whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SecondExistingProductAllocationBase whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SecondExistingProductAllocationBase whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SecondExistingProductAllocationBase whereExistingProductsTarget($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SecondExistingProductAllocationBase whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SecondExistingProductAllocationBase whereTotalExistingTarget($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SecondExistingProductAllocationBase whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SecondExistingProductAllocationBase whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SecondExistingProductAllocationBase whereUseModifiedTargets($value)
 * @mixin \Eloquent
 */
class SecondExistingProductAllocationBase extends Model
{

    protected $table = 'second_existing_products_allocation_base';
    
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
