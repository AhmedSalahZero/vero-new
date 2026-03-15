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
 * @property int $id
 * @property string $name
 * @property int $company_id
 * @property int|null $creator_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company $company
 * @property-read \App\Models\User|null $creator
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ServiceCategory> $serviceCategories
 * @property-read int|null $service_categories_count
 * @property-read bool|null $service_categories_exists
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ServiceItem> $serviceItems
 * @property-read int|null $service_items_count
 * @property-read bool|null $service_items_exists
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\BusinessSector newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\BusinessSector newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\BusinessSector onlyCurrentCompany(?int $companyId = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\BusinessSector query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\BusinessSector whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\BusinessSector whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\BusinessSector whereCreatorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\BusinessSector whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\BusinessSector whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\BusinessSector whereUpdatedAt($value)
 * @mixin \Eloquent
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
