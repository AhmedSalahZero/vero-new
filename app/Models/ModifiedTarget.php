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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ModifiedTarget company()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ModifiedTarget newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ModifiedTarget newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ModifiedTarget query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ModifiedTarget whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ModifiedTarget whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ModifiedTarget whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ModifiedTarget whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ModifiedTarget whereOthersTarget($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ModifiedTarget whereProductsModifiedTargets($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ModifiedTarget whereSalesTargetsPercentages($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ModifiedTarget whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ModifiedTarget whereUseModifiedTargets($value)
 * @mixin \Eloquent
 */
class ModifiedTarget extends Model
{
   
    protected $table = 'modified_targe';
       
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
