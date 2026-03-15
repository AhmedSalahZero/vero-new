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
 * @property-read \App\Models\Category|null $category
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Product company()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Product query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Product whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Product whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Product whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Product whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Product whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Product whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Product whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Product whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Product whereUpdatedBy($value)
 * @mixin \Eloquent
 */
class Product extends Model
{
   
    protected $table = 'products';
    
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
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }
}
