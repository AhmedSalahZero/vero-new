<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuantityCategory extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'quantity_categories';
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
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
