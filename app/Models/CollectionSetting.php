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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CollectionSetting company()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CollectionSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CollectionSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CollectionSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CollectionSetting whereCollectionBase($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CollectionSetting whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CollectionSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CollectionSetting whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CollectionSetting whereFirstAllocationCollection($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CollectionSetting whereGeneralCollection($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CollectionSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CollectionSetting whereSecondAllocationCollection($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CollectionSetting whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CollectionSetting extends Model
{
    
    protected $table = 'collection_settings';
   
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
