<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int|null $company_id
 * @property array<array-key, mixed> $sales_targets_percentages
 * @property int $use_modified_targets
 * @property array<array-key, mixed>|null $others_target
 * @property array<array-key, mixed>|null $products_modified_targets
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityModifiedTarget company()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityModifiedTarget newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityModifiedTarget newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityModifiedTarget query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityModifiedTarget whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityModifiedTarget whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityModifiedTarget whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityModifiedTarget whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityModifiedTarget whereOthersTarget($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityModifiedTarget whereProductsModifiedTargets($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityModifiedTarget whereSalesTargetsPercentages($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityModifiedTarget whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityModifiedTarget whereUseModifiedTargets($value)
 * @mixin \Eloquent
 */
class QuantityModifiedTarget extends Model
{
    
    protected $table = 'quantity_modified_targe';
       
    protected $guarded = [];
    
    protected $casts = [
        'products_modified_targets' => 'array',
        'others_target' => 'array',
        'sales_targets_percentages' => 'array',
    ];

    // Company Scoop
    public function scopeCompany($query)
    {
        return $query->where('company_id', request()->company->id);
    }

}
