<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int|null $company_id
 * @property string|null $name
 * @property string $type
 * @property string|null $updated_by
 * @property string|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $products
 * @property-read int|null $products_count
 * @property-read bool|null $products_exists
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Category company()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Category query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Category whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Category whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Category whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Category whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Category whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Category whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Category whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Category whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\Category whereUpdatedBy($value)
 * @mixin \Eloquent
 */
class Category extends Model
{
 
    protected $table = 'categories';
 
    protected $guarded = [];

    // Company Scoop
    public function scopeCompany($query)
    {
        return $query->where('company_id', request()->company->id);
    }
    /**
     * Get all of the comments for the Category
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }
    public static function boot() {
        parent::boot();

        static::deleting(function($category) { // before delete() method call this
             $category->products()->delete();
        });
    }
}
