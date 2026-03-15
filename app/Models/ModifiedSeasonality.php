<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int|null $company_id
 * @property int $number_of_products
 * @property int $use_modified_seasonality
 * @property array<array-key, mixed>|null $original_seasonality
 * @property array<array-key, mixed>|null $modified_seasonality
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ModifiedSeasonality company()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ModifiedSeasonality newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ModifiedSeasonality newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ModifiedSeasonality query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ModifiedSeasonality whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ModifiedSeasonality whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ModifiedSeasonality whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ModifiedSeasonality whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ModifiedSeasonality whereModifiedSeasonality($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ModifiedSeasonality whereNumberOfProducts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ModifiedSeasonality whereOriginalSeasonality($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ModifiedSeasonality whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\ModifiedSeasonality whereUseModifiedSeasonality($value)
 * @mixin \Eloquent
 */
class ModifiedSeasonality extends Model
{
    protected $table = 'modified_seasonality';
       
    protected $guarded = [];
    
    protected $casts = [
        'original_seasonality' => 'array',
        'modified_seasonality' => 'array',
    ];

    // Company Scoop
    public function scopeCompany($query)
    {
        return $query->where('company_id', request()->company->id);
    }
}
