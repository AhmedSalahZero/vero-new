<?php

namespace App\Models;

use App\Models\Traits\Accessors\BusinessSectorAccessor;
use App\Models\Traits\Mutators\BusinessSectorMutator;
use App\Models\Traits\Relations\BusinessSectorRelation;
use App\Models\Traits\Scopes\CompanyScope;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperBusinessSector
 */
class BusinessSector extends Model  
{
    use BusinessSectorRelation ,BusinessSectorAccessor  , BusinessSectorMutator , CompanyScope,HasCompany;

    protected $guarded = [
        'id'
    ];

    public static function getCrudFormName():string 
    {
        return 'admin.business-sectors.form';
    }
    public static function getPageTitle()
    {
        return __('Business Sectors');
    }
     public static function getViewVars():array 
    {
        $currentCompanyId =  getCurrentCompanyId();
        return [
            'modelName'=>'BusinessSector',
            'storeRoute'=>route('admin.store.business.sector',$currentCompanyId),
            'pageTitle'=>static::getPageTitle(),
            'type'=>'create'
        ];
        
    }
	public function creator():BelongsTo
	{
		return $this->belongsTo(User::class, 'creator_id', 'id');
	}
	public function serviceCategories():HasMany
	{
		return $this->hasMany(ServiceCategory::class, 'business_sector_id', 'id');
	}
	public function serviceItems():HasMany
	{
		return $this->hasMany(ServiceItem::class, 'business_sector_id', 'id');
	}
    
}
