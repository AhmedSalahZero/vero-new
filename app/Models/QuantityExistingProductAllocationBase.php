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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityExistingProductAllocationBase company()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityExistingProductAllocationBase newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityExistingProductAllocationBase newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityExistingProductAllocationBase query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityExistingProductAllocationBase whereAllocationBase($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityExistingProductAllocationBase whereAllocationBasePercentages($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityExistingProductAllocationBase whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityExistingProductAllocationBase whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityExistingProductAllocationBase whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityExistingProductAllocationBase whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityExistingProductAllocationBase whereExistingProductsTarget($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityExistingProductAllocationBase whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityExistingProductAllocationBase whereTotalExistingTarget($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityExistingProductAllocationBase whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityExistingProductAllocationBase whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityExistingProductAllocationBase whereUseModifiedTargets($value)
 * @mixin \Eloquent
 */
class QuantityExistingProductAllocationBase extends Model
{
 
    protected $table = 'quantity_existing_products_allocation_base';
    
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
