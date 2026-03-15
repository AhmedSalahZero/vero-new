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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityModifiedSeasonality company()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityModifiedSeasonality newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityModifiedSeasonality newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityModifiedSeasonality query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityModifiedSeasonality whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityModifiedSeasonality whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityModifiedSeasonality whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityModifiedSeasonality whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityModifiedSeasonality whereModifiedSeasonality($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityModifiedSeasonality whereNumberOfProducts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityModifiedSeasonality whereOriginalSeasonality($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityModifiedSeasonality whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityModifiedSeasonality whereUseModifiedSeasonality($value)
 * @mixin \Eloquent
 */
class QuantityModifiedSeasonality extends Model
{
    
    protected $table = 'quantity_modified_seasonality';
       
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
