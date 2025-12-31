<?php

namespace App\Models;
use App\Traits\StaticBoot;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Section extends Model
{
     use SoftDeletes,StaticBoot;
    protected $guarded = [];
    protected $casts = ['name' => 'array'];

    // protected $with = [
    //     'subSections'
    // ];

    // protected static function booted()
    // {
    //     static::addGlobalScope('sub_of', function (Builder $builder) {
    //         $builder->where('sub_of',0);
    //     });
    // }
    public function scopeMainSections($query)
    {
        return $query->where('sub_of',0);
    }

    /**
     * Get the
     *
     * @param  string  $value
     * @return string
     */
    public function getRouteNameAttribute()
    {
        $route = $this->route;
        $route_array = explode('.',$route);
        $route = $route_array[0];
        return $route;
    }
    public function scopeMainClientSideSections($query)
    {
        return $query->where('sub_of',0)->where('section_side','client')->where('trash',0);
    }
    public function scopeMainSuperAdminSections($query)
    {
        return $query->where('sub_of',0)->where('section_side','admin')->where('trash',0);
    }
	public function scopeMainCompanyAdminSections($query)
    {
        return $query->where('sub_of',0)->where('section_side','company-admin')->where('trash',0);
    }
    public function parent()
    {
        return $this->belongsTo(Section::class, 'sub_of', 'id');
    }
    public function subSections()
    {
        return $this->hasMany(Section::class, 'sub_of', 'id')->where('trash',0);
    }
    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'branches_sections');
    }
	public function isExportable(array $exportables)
	{
		if(str_contains($this->route,'.products.') && in_array('product_or_service',array_keys($exportables))){
			return true ;
		}if(str_contains($this->route,'.Items.') && in_array('product_item',array_keys($exportables))){
			return true ;
		}
		return false;
	}
}
