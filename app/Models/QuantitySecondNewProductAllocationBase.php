<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int|null $company_id
 * @property string $allocation_base
 * @property array<array-key, mixed>|null $new_allocation_bases_names
 * @property array<array-key, mixed>|null $allocation_base_data
 * @property string|null $updated_by
 * @property string|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantitySecondNewProductAllocationBase company()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantitySecondNewProductAllocationBase newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantitySecondNewProductAllocationBase newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantitySecondNewProductAllocationBase query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantitySecondNewProductAllocationBase whereAllocationBase($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantitySecondNewProductAllocationBase whereAllocationBaseData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantitySecondNewProductAllocationBase whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantitySecondNewProductAllocationBase whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantitySecondNewProductAllocationBase whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantitySecondNewProductAllocationBase whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantitySecondNewProductAllocationBase whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantitySecondNewProductAllocationBase whereNewAllocationBasesNames($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantitySecondNewProductAllocationBase whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantitySecondNewProductAllocationBase whereUpdatedBy($value)
 * @mixin \Eloquent
 */
class QuantitySecondNewProductAllocationBase extends Model
{
 
    protected $table = 'quantity_second_new_products_allocation_base';
    
    protected $guarded = [];

    
    protected $casts = [
        'allocation_base_data' => 'array',
        'new_allocation_bases_names' => 'array',
    ];
    // Company Scoop
    public function scopeCompany($query)
    {
        return $query->where('company_id', request()->company->id);
    }
}
