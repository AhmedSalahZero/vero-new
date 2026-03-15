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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NewProductAllocationBase company()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NewProductAllocationBase newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NewProductAllocationBase newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NewProductAllocationBase query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NewProductAllocationBase whereAllocationBase($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NewProductAllocationBase whereAllocationBaseData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NewProductAllocationBase whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NewProductAllocationBase whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NewProductAllocationBase whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NewProductAllocationBase whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NewProductAllocationBase whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NewProductAllocationBase whereNewAllocationBasesNames($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NewProductAllocationBase whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\NewProductAllocationBase whereUpdatedBy($value)
 * @mixin \Eloquent
 */
class NewProductAllocationBase extends Model
{

    protected $table = 'new_products_allocation_base';
    
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
