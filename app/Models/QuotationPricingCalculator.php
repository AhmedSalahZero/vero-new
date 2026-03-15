<?php

namespace App\Models;

use App\Interfaces\Models\IExportable;
use App\Interfaces\Models\IHaveAllRelations;
use App\Interfaces\Models\IShareable;
use App\Models\Repositories\CurrencyRepository;
use App\Models\Repositories\CustomerRepository;
use App\Models\Repositories\PositionRepository;
use App\Models\Repositories\RevenueBusinessLineRepository;
use App\Models\Repositories\ServiceCategoryRepository;
use App\Models\Repositories\ServiceItemRepository;
use App\Models\Repositories\ServiceNatureRepository;
use App\Models\Traits\Accessors\QuotationPricingCalculatorAccessor;
use App\Models\Traits\Mutators\QuotationPricingCalculatorMutator;
use App\Models\Traits\Relations\QuotationPricingCalculatorRelation;
use App\Models\Traits\Scopes\CompanyScope;
use App\Models\Traits\Scopes\Globals\StateCountryScope;
use App\Models\Traits\Scopes\withAllRelationsScope;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int|null $customer_id
 * @property int|null $business_sector_id
 * @property string|null $name
 * @property string $date
 * @property int|null $country_id
 * @property int|null $state_id
 * @property int|null $currency_id
 * @property float $price_sensitivity
 * @property int $use_freelancer
 * @property string $total_recommend_price_without_vat
 * @property string $total_recommend_price_with_vat
 * @property string $price_per_day_without_vat
 * @property string $price_per_day_with_vat
 * @property string $total_net_profit_after_taxes
 * @property string $net_profit_after_taxes_per_day
 * @property string $total_sensitive_price_without_vat
 * @property string $total_sensitive_price_with_vat
 * @property string $sensitive_price_per_day_without_vat
 * @property string $sensitive_price_per_day_with_vat
 * @property string $sensitive_total_net_profit_after_taxes
 * @property string $sensitive_net_profit_after_taxes_per_day
 * @property string $sensitive_net_profit_after_taxes_percentage
 * @property int $company_id
 * @property int|null $creator_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\Country|null $country
 * @property-read \App\Models\User|null $creator
 * @property-read \App\Models\Currency|null $currency
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Position> $directManpowerExpensePositions
 * @property-read int|null $direct_manpower_expense_positions_count
 * @property-read bool|null $direct_manpower_expense_positions_exists
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ServiceItem> $directManpowerExpenseServiceItems
 * @property-read int|null $direct_manpower_expense_service_items_count
 * @property-read bool|null $direct_manpower_expense_service_items_exists
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DirectManpowerExpense> $directManpowerExpenses
 * @property-read int|null $direct_manpower_expenses_count
 * @property-read bool|null $direct_manpower_expenses_exists
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Position> $freelancerExpensePositions
 * @property-read int|null $freelancer_expense_positions_count
 * @property-read bool|null $freelancer_expense_positions_exists
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FreelancerExpense> $freelancerExpenses
 * @property-read int|null $freelancer_expenses_count
 * @property-read bool|null $freelancer_expenses_exists
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GeneralExpense> $generalExpenses
 * @property-read int|null $general_expenses_count
 * @property-read bool|null $general_expenses_exists
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OtherDirectOperationExpense> $otherDirectOperationExpenses
 * @property-read int|null $other_direct_operation_expenses_count
 * @property-read bool|null $other_direct_operation_expenses_exists
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OtherVariableManpowerExpense> $otherVariableManpowerExpenses
 * @property-read int|null $other_variable_manpower_expenses_count
 * @property-read bool|null $other_variable_manpower_expenses_exists
 * @property-read \App\Models\Profitability|null $profitability
 * @property-read \App\Models\RevenueBusinessLine|null $revenueBusinessLine
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RevenueBusinessLine> $revenueBusinessLines
 * @property-read int|null $revenue_business_lines_count
 * @property-read bool|null $revenue_business_lines_exists
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SalesAndMarketingExpense> $salesAndMarketingExpenses
 * @property-read int|null $sales_and_marketing_expenses_count
 * @property-read bool|null $sales_and_marketing_expenses_exists
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ServiceCategory> $serviceCategories
 * @property-read int|null $service_categories_count
 * @property-read bool|null $service_categories_exists
 * @property-read \App\Models\ServiceCategory|null $serviceCategory
 * @property-read \App\Models\ServiceItem|null $serviceItem
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ServiceItem> $serviceItems
 * @property-read int|null $service_items_count
 * @property-read bool|null $service_items_exists
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ServiceNature> $serviceNatures
 * @property-read int|null $service_natures_count
 * @property-read bool|null $service_natures_exists
 * @property-read \App\Models\State|null $state
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuotationPricingCalculator newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuotationPricingCalculator newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuotationPricingCalculator onlyCurrentCompany(?int $companyId = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuotationPricingCalculator query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuotationPricingCalculator whereBusinessSectorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuotationPricingCalculator whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuotationPricingCalculator whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuotationPricingCalculator whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuotationPricingCalculator whereCreatorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuotationPricingCalculator whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuotationPricingCalculator whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuotationPricingCalculator whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuotationPricingCalculator whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuotationPricingCalculator whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuotationPricingCalculator whereNetProfitAfterTaxesPerDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuotationPricingCalculator wherePricePerDayWithVat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuotationPricingCalculator wherePricePerDayWithoutVat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuotationPricingCalculator wherePriceSensitivity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuotationPricingCalculator whereSensitiveNetProfitAfterTaxesPerDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuotationPricingCalculator whereSensitiveNetProfitAfterTaxesPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuotationPricingCalculator whereSensitivePricePerDayWithVat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuotationPricingCalculator whereSensitivePricePerDayWithoutVat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuotationPricingCalculator whereSensitiveTotalNetProfitAfterTaxes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuotationPricingCalculator whereStateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuotationPricingCalculator whereTotalNetProfitAfterTaxes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuotationPricingCalculator whereTotalRecommendPriceWithVat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuotationPricingCalculator whereTotalRecommendPriceWithoutVat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuotationPricingCalculator whereTotalSensitivePriceWithVat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuotationPricingCalculator whereTotalSensitivePriceWithoutVat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuotationPricingCalculator whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuotationPricingCalculator whereUseFreelancer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuotationPricingCalculator withAllRelations(?int $companyId = null)
 * @mixin \Eloquent
 */
class QuotationPricingCalculator extends Model implements  IHaveAllRelations , IExportable , IShareable
{
    use  QuotationPricingCalculatorAccessor,QuotationPricingCalculatorMutator , QuotationPricingCalculatorRelation , CompanyScope , withAllRelationsScope ;
         
    public function getName(): string { return ''; }
    
    public function getRouteKeyName()
    {
        return 'quotation_pricing_calculators.id' ;
    }
    public static function getShareableEditViewVars($model):array 
    {
        
        return [
            'pageTitle'=>QuotationPricingCalculator::getPageTitle(),
             'revenueBusinessLines'=>App(RevenueBusinessLineRepository::class)->oneFormattedForSelect($model),
            'serviceCategories'=>App(ServiceCategoryRepository::class)->oneFormattedForSelect($model),
            'serviceItems'=>App(ServiceItemRepository::class)->oneFormattedForSelect($model),
            'serviceNatures'=>App(ServiceNatureRepository::class)->oneFormattedForSelect($model),
            'positions'=>App(PositionRepository::class)->oneFormattedForSelect($model),

            'currencies'=>App(CurrencyRepository::class)->oneFormattedForSelect($model),
 //           'customersAndLeads'=>App(CustomerRepository::class)->formattedForSelect($model)
            
        ];   
    }
    
    public static function exportViewName():string {
        return __('Quotation Pricing Calculator'); 
    }
     public static function getFileName():string 
    {
        return __('Quotation Pricing Calculator');
    }

    protected static function booted()
    {
        static::addGlobalScope(new StateCountryScope);
    }

    public static function getCrudViewName():string 
    {
        return 'admin.quotation-pricing-calculator.create';
    }
    
    public static function getViewVars():array 
    {
        $currentCompanyId =  getCurrentCompanyId();
        
        return [
            'getDataRoute'=>route('admin.get.quotation.pricing.calculator' , ['company'=>$currentCompanyId]) ,
            'modelName'=>'QuotationPricingCalculator',
            'exportRoute'=>route('admin.export.quotation.pricing.calculator' , $currentCompanyId),
            'createRoute'=>route('admin.create.quotation.pricing.calculator',$currentCompanyId),
            'storeRoute'=>route('admin.store.quotation.pricing.calculator',$currentCompanyId),
            // 'updateRoute'=>route('admin.update.quotation.pricing.calculator',[$currentCompanyId ]),
            'hasChildRows'=>true ,
            'pageTitle'=>QuotationPricingCalculator::getPageTitle(),
             'revenueBusinessLines'=>App(RevenueBusinessLineRepository::class)->allFormattedForSelect(),
            'serviceCategories'=>App(ServiceCategoryRepository::class)->allFormattedForSelect(),
            'serviceItems'=>App(ServiceItemRepository::class)->allFormattedForSelect(),
            'serviceNatures'=>App(ServiceNatureRepository::class)->allFormattedForSelect(),
            'positions'=>App(PositionRepository::class)->allFormattedForSelect($currentCompanyId),
            'currencies'=>App(CurrencyRepository::class)->allFormattedForSelect(),
 //            'customersAndLeads'=>App(CustomerRepository::class)->allFormattedForSelect($currentCompanyId),
            'redirectAfterSubmitRoute'=>route('admin.view.quotation.pricing.calculator',$currentCompanyId),
            'type'=>'create'
        ];
        
    }

    public static function getPageTitle()
    {
        return __('Quotation Pricing Calculator'); 
    }
    

    public function getAllRelationsNames():array 
    {
        return [
            'revenueBusinessLine',
            'serviceCategory','serviceItem','serviceNatureRelation','currency','otherVariableManpowerExpenses',
            'directManpowerExpenses','salesAndMarketingExpenses','otherDirectOperationExpenses','generalExpenses','freelancerExpensePositions',
            'directManpowerExpensePositions','freelancerExpenses','profitability'
        ];
    }
    
    
}
