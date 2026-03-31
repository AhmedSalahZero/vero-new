<?php

namespace App\Models;

use App\Interfaces\Models\IExportable;
use App\Interfaces\Models\IHaveCompany;
use App\Interfaces\Models\IHaveCreator;
use App\Interfaces\Models\IHaveView;
use App\Models\Repositories\RevenueBusinessLineRepository;
use App\Models\Repositories\ServiceCategoryRepository;
use App\Models\Repositories\ServiceItemRepository;
use App\Models\Traits\Accessors\RevenueBusinessLineAccessor;
use App\Models\Traits\Mutators\RevenueBusinessLineMutator;
use App\Models\Traits\Relations\RevenueBusinessLineRelation;
use App\Models\Traits\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $name
 * @property int $id
 * @property string $revenueBusinessLineName
 * @property string $creator_name
 * @property int $order
 * @property int $company_id
 * @property int|null $creator_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $created_at_formatted
 * @property string $updated_at_formatted
 * @property \Illuminate\Database\Eloquent\Collection<int, \App\Models\ServiceCategory> $serviceCategories
 * @property \Illuminate\Database\Eloquent\Collection<int, \App\Models\ServiceItem> $serviceItems
 * @property int $company_id
 * @property int|null $creator_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\QuotationPricingCalculator> $QuotationPricingCalculators
 * @property-read int|null $quotation_pricing_calculators_count
 * @property-read bool|null $quotation_pricing_calculators_exists
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\User|null $creator
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\QuickPricingCalculator> $quickPricingCalculators
 * @property-read int|null $quick_pricing_calculators_count
 * @property-read bool|null $quick_pricing_calculators_exists
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ServiceCategory> $serviceCategories
 * @property-read int|null $service_categories_count
 * @property-read bool|null $service_categories_exists
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ServiceItem> $serviceItems
 * @property-read int|null $service_items_count
 * @property-read bool|null $service_items_exists
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\RevenueBusinessLine forCurrentCompany()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\RevenueBusinessLine newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\RevenueBusinessLine newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\RevenueBusinessLine onlyCurrentCompany(?int $companyId = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\RevenueBusinessLine query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\RevenueBusinessLine whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\RevenueBusinessLine whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\RevenueBusinessLine whereCreatorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\RevenueBusinessLine whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\RevenueBusinessLine whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\RevenueBusinessLine whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class RevenueBusinessLine extends Model implements IHaveView,IHaveCompany,IHaveCreator, IExportable
{ 
    use RevenueBusinessLineAccessor,RevenueBusinessLineMutator , RevenueBusinessLineRelation   , CompanyScope ;
    protected $guarded = [
        'id'
    ];
    
    public static function getFileName():string 
    {
        return __('Revenue Business Line');
    }
    
    public static function exportViewName():string {
        return __('Revenue Business Line'); 
    }
    
    public static function getViewVars():array 
    {
        $currentCompanyId = app(Company::class)->getIdentifier() ;
        return [
            'getDataRoute'=>route('admin.get.revenue-business-line' , ['company'=>getCompanyId()]) ,
            'modelName'=>'RevenueBusinessLine',
            // 'companies'=>App(CompanyRepository::class)->allFormatted(),
            'exportRoute'=>route('admin.export.revenue-business-line' , $currentCompanyId),
            'createRoute'=>route('admin.create.revenue-business-line',$currentCompanyId),
            'pageTitle'=>__('Revenue Business Line'),
              'revenueBusinessLines'=>App(RevenueBusinessLineRepository::class)->allFormattedForSelect(),
            'serviceCategories'=>App(ServiceCategoryRepository::class)->allFormattedForSelect(),
            'serviceItems'=>App(ServiceItemRepository::class)->allFormattedForSelect()
        ];
    }
    
    public function scopeForCurrentCompany(Builder $builder)
    {
		/** @phpstan-ignore-next-line */
		if(!app(Company::class)){
			return $builder;
		}
        return $builder->where('company_id' , app(Company::class)->id);
    }
	public static function removeUnusedCategories()
	{
		$companyId = getCurrentCompanyId();
		$serviceCategories = ServiceCategory::where('company_id',$companyId)->get();
		foreach($serviceCategories as $serviceCategory){
			if(!$serviceCategory->serviceItems->count()){
				try{
					$serviceCategory->delete();
				}
				catch(\Exception $e){
					
				}
			}
		}
		$revenueBusinessLines = RevenueBusinessLine::where('company_id',$companyId)->get();
		foreach($revenueBusinessLines as $revenueBusinessLine){
			if(!$revenueBusinessLine->serviceCategories->count()){
				try{
					$revenueBusinessLine->delete();
				}
				catch(\Exception $e){
					
				}
			}
		}
	}
}
