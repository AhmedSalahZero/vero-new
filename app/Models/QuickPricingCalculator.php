<?php

namespace App\Models;

use App\Helpers\HHelpers;
use App\Interfaces\Models\IExportable;
use App\Interfaces\Models\IHaveAllRelations;
use App\Models\Repositories\CurrencyRepository;
use App\Models\Repositories\PositionRepository;
use App\Models\Repositories\RevenueBusinessLineRepository;
use App\Models\Repositories\ServiceCategoryRepository;
use App\Models\Repositories\ServiceItemRepository;
use App\Models\Repositories\ServiceNatureRepository;
use App\Models\Traits\Accessors\QuickPricingCalculatorAccessor;
use App\Models\Traits\Mutators\QuickPricingCalculatorMutator;
use App\Models\Traits\Relations\QuickPricingCalculatorRelation;
use App\Models\Traits\Scopes\CompanyScope;
use App\Models\Traits\Scopes\withAllRelationsScope;
use Illuminate\Database\Eloquent\Model;
/**
 * @property string|null $customer_name
 * @property string|null $revenueBusinessLineName
 * @property string|null $serviceCategoryName
 * @property string|null $serviceItemName
 * @property string|null $totalRecommendPriceWithoutVatFormatted
 * @property string|null $totalRecommendPriceWithVatFormatted
 * @property string|null $totalNetProfitAfterTaxesFormatted
 * @property string|null $creator_name
 * @property string|null $created_at_formatted
 * @property int $order
 * @property int $id
 * @property int|null $pricing_plan_id
 * @property int $revenue_business_line_id
 * @property int $service_category_id
 * @property int $service_item_id
 * @property int $service_nature_id
 * @property float $delivery_days
 * @property string|null $name
 * @property string $date
 * @property int|null $customer_id
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
 * @property-read \App\Models\Partner|null $customer
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Position> $directManpowerExpensePositions
 * @property-read int|null $direct_manpower_expense_positions_count
 * @property-read bool|null $direct_manpower_expense_positions_exists
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
 * @property-read \App\Models\PricingPlan|null $pricingPlan
 * @property-read \App\Models\Profitability|null $profitability
 * @property-read \App\Models\RevenueBusinessLine|null $revenueBusinessLine
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SalesAndMarketingExpense> $salesAndMarketingExpenses
 * @property-read int|null $sales_and_marketing_expenses_count
 * @property-read bool|null $sales_and_marketing_expenses_exists
 * @property-read \App\Models\ServiceCategory|null $serviceCategory
 * @property-read \App\Models\ServiceItem|null $serviceItem
 * @property-read \App\Models\ServiceNature|null $serviceNature
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SharingLink> $sharingLinks
 * @property-read int|null $sharing_links_count
 * @property-read bool|null $sharing_links_exists
 * @property-read \App\Models\State|null $state
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuickPricingCalculator newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuickPricingCalculator newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuickPricingCalculator onlyCurrentCompany(?int $companyId = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuickPricingCalculator query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuickPricingCalculator whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuickPricingCalculator whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuickPricingCalculator whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuickPricingCalculator whereCreatorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuickPricingCalculator whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuickPricingCalculator whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuickPricingCalculator whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuickPricingCalculator whereDeliveryDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuickPricingCalculator whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuickPricingCalculator whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuickPricingCalculator whereNetProfitAfterTaxesPerDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuickPricingCalculator wherePricePerDayWithVat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuickPricingCalculator wherePricePerDayWithoutVat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuickPricingCalculator wherePriceSensitivity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuickPricingCalculator wherePricingPlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuickPricingCalculator whereRevenueBusinessLineId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuickPricingCalculator whereSensitiveNetProfitAfterTaxesPerDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuickPricingCalculator whereSensitiveNetProfitAfterTaxesPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuickPricingCalculator whereSensitivePricePerDayWithVat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuickPricingCalculator whereSensitivePricePerDayWithoutVat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuickPricingCalculator whereSensitiveTotalNetProfitAfterTaxes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuickPricingCalculator whereServiceCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuickPricingCalculator whereServiceItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuickPricingCalculator whereServiceNatureId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuickPricingCalculator whereStateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuickPricingCalculator whereTotalNetProfitAfterTaxes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuickPricingCalculator whereTotalRecommendPriceWithVat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuickPricingCalculator whereTotalRecommendPriceWithoutVat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuickPricingCalculator whereTotalSensitivePriceWithVat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuickPricingCalculator whereTotalSensitivePriceWithoutVat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuickPricingCalculator whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuickPricingCalculator whereUseFreelancer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\QuickPricingCalculator withAllRelations(?int $companyId = null)
 * @mixin \Eloquent
 */

class QuickPricingCalculator extends Model implements  IHaveAllRelations, IExportable
{
    use   QuickPricingCalculatorAccessor;
    use QuickPricingCalculatorMutator ;
    use QuickPricingCalculatorRelation ;
    use CompanyScope ;
    use withAllRelationsScope ;

  

    public function getRouteKeyName()
    {
        return 'quick_pricing_calculators.id' ;
    }

    public static function exportViewName(): string
    {
        return __('Quick Pricing Calculator');
    }

    public static function getFileName(): string
    {
        return __('Quick Pricing Calculator');
    }

    protected static function booted()
    {
        // static::addGlobalScope(new StateCountryScope);
    }

    public static function getCrudViewName(): string
    {
        return 'admin.quick-pricing-calculator.create';
    }

    public static function getViewVars(): array
    {
        $currentCompanyId = getCurrentCompanyId();
        return [
            'getDataRoute' => route('admin.get.quick.pricing.calculator', ['company' => $currentCompanyId]),
            'modelName' => 'QuickPricingCalculator',
            'exportRoute' => route('admin.export.quick.pricing.calculator', $currentCompanyId),
            'createRoute' => route('admin.create.quick.pricing.calculator', $currentCompanyId),
            'storeRoute' => route('admin.store.quick.pricing.calculator', $currentCompanyId),
            'hasChildRows' => true,
            'pageTitle' => QuickPricingCalculator::getPageTitle(),
            'revenueBusinessLines' => App(RevenueBusinessLineRepository::class)->allFormattedForSelect(),
            'serviceCategories' => App(ServiceCategoryRepository::class)->allFormattedForSelect(),
            'serviceItems' => App(ServiceItemRepository::class)->allFormattedForSelect(),
            'serviceNatures' => App(ServiceNatureRepository::class)->allFormattedForSelect(),
            'pricingPlans' => PricingPlan::allFormattedForSelect($currentCompanyId),
            'directManpowerExpensePositions' => App(PositionRepository::class)->allFormattedForSelect('direct-manpower-expense'),
            'freelancerExpensePositions' => App(PositionRepository::class)->allFormattedForSelect('freelancer-expenses'),
            'otherVariableManpowerExpenses' => PricingExpense::allFormattedForSelect('other-direct-manpower-expense', $currentCompanyId),
            'otherDirectOperationsExpenses' => PricingExpense::allFormattedForSelect('other-direct-operations-expense', $currentCompanyId),
            'salesAndMarketExpenses' => PricingExpense::allFormattedForSelect('sales-and-market-expense', $currentCompanyId),
            'generalExpenses' => PricingExpense::allFormattedForSelect('general-and-administrative-expense', $currentCompanyId),
            'currencies' => App(CurrencyRepository::class)->allFormattedForSelect(),
            'redirectAfterSubmitRoute' => route('admin.view.quick.pricing.calculator', ['company' => $currentCompanyId, 'active' => 'quick-price-calculator']),
            'type' => 'create',
			'customers'=>HHelpers::formatForSelect2(Partner::where('company_id',$currentCompanyId)->onlyCustomers()->pluck('name','id')->toArray())
        ];
    }

    public static function getPageTitle(): string
    {
        return __('Quick Pricing Calculator') ;
    }

    public function getAllRelationsNames(): array
    {
        return [
            'revenueBusinessLine',
            'serviceCategory', 'serviceItem', 'serviceNatureRelation', 'currency', 'otherVariableManpowerExpenses',
            'directManpowerExpenses', 'salesAndMarketingExpenses', 'otherDirectOperationExpenses', 'generalExpenses', 'freelancerExpensePositions',
            'directManpowerExpensePositions', 'freelancerExpenses', 'profitability'
        ];
    }
}
