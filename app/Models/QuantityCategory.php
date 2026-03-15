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
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\QuantityProduct> $products
 * @property-read int|null $products_count
 * @property-read bool|null $products_exists
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityCategory company()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityCategory whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityCategory whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityCategory whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityCategory whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityCategory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuantityCategory whereUpdatedBy($value)
 * @mixin \Eloquent
 */
class QuantityCategory extends Model
{
   
    protected $table = 'quantity_categories';
    
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
        return $this->hasMany(QuantityProduct::class,'category_id','id');
    }
    public static function boot() {
        parent::boot();

        static::deleting(function($category) { // before delete() method call this
             $category->products()->delete();
        });
    }
}
