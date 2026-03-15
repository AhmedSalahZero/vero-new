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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SecondNewProductAllocationBase company()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SecondNewProductAllocationBase newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SecondNewProductAllocationBase newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SecondNewProductAllocationBase query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SecondNewProductAllocationBase whereAllocationBase($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SecondNewProductAllocationBase whereAllocationBaseData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SecondNewProductAllocationBase whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SecondNewProductAllocationBase whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SecondNewProductAllocationBase whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SecondNewProductAllocationBase whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SecondNewProductAllocationBase whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SecondNewProductAllocationBase whereNewAllocationBasesNames($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SecondNewProductAllocationBase whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\SecondNewProductAllocationBase whereUpdatedBy($value)
 * @mixin \Eloquent
 */
class SecondNewProductAllocationBase extends Model
{
  
    protected $table = 'second_new_products_allocation_base';
    
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
