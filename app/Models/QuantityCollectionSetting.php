<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int|null $company_id
 * @property string $collection_base
 * @property array<array-key, mixed>|null $general_collection
 * @property array<array-key, mixed>|null $first_allocation_collection
 * @property array<array-key, mixed>|null $second_allocation_collection
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityCollectionSetting company()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityCollectionSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityCollectionSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityCollectionSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityCollectionSetting whereCollectionBase($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityCollectionSetting whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityCollectionSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityCollectionSetting whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityCollectionSetting whereFirstAllocationCollection($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityCollectionSetting whereGeneralCollection($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityCollectionSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityCollectionSetting whereSecondAllocationCollection($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityCollectionSetting whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class QuantityCollectionSetting extends Model
{

    protected $table = 'quantity_collection_settings';
    
    protected $guarded = [];
    
    protected $casts = [
        'general_collection' => 'array',
        'first_allocation_collection' => 'array',
        'second_allocation_collection' => 'array',
    ];
    // Company Scoop
    public function scopeCompany($query)
    {
        return $query->where('company_id', request()->company->id);
    }
}
