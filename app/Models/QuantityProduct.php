<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int|null $company_id
 * @property string|null $name
 * @property string $type
 * @property int $category_id
 * @property string|null $updated_by
 * @property string|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\QuantityCategory|null $category
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityProduct company()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityProduct newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityProduct newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityProduct query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityProduct whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityProduct whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityProduct whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityProduct whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityProduct whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityProduct whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityProduct whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityProduct whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityProduct whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityProduct whereUpdatedBy($value)
 * @mixin \Eloquent
 */
class QuantityProduct extends Model
{
  
    protected $table = 'quantity_products';
    
    protected $guarded = [];

    // Company Scoop
    public function scopeCompany($query)
    {
        return $query->where('company_id', request()->company->id);
    }

    /**
     * Get the user that owns the Product
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(QuantityCategory::class, 'category_id', 'id');
    }
}
