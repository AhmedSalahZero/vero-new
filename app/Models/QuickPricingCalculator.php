<?php

namespace App\Models;

use App\Helpers\HHelpers;
use App\Interfaces\Models\IBaseModel;
use App\Interfaces\Models\IExportable;
use App\Interfaces\Models\IHaveAllRelations;
use App\Interfaces\Models\IShareable;
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
use App\Models\Traits\Scopes\Globals\StateCountryScope;
use App\Models\Traits\Scopes\withAllRelationsScope;
use Illuminate\Database\Eloquent\Model;

class QuickPricingCalculator extends Model implements IBaseModel, IHaveAllRelations, IExportable, IShareable
{
    use   QuickPricingCalculatorAccessor;
    use QuickPricingCalculatorMutator ;
    use QuickPricingCalculatorRelation ;
    use CompanyScope ;
    use withAllRelationsScope ;

    public static function getShareableEditViewVars($model): array
    {
        return [
            'pageTitle' => QuickPricingCalculator::getPageTitle(),
            'revenueBusinessLines' => App(RevenueBusinessLineRepository::class)->oneFormattedForSelect($model),
            'serviceCategories' => App(ServiceCategoryRepository::class)->oneFormattedForSelect($model),
            'serviceItems' => App(ServiceItemRepository::class)->oneFormattedForSelect($model),
            'serviceNatures' => App(ServiceNatureRepository::class)->oneFormattedForSelect($model),
            'pricingPlans' => PricingPlan::oneFormattedForSelect($model),
            'directManpowerExpensePositions' => App(PositionRepository::class)->oneFormattedForSelect($model, 'direct-manpower-expense'),
            'freelancerExpensePositions' => App(PositionRepository::class)->oneFormattedForSelect($model, 'freelancer-expenses'),
            'otherVariableManpowerExpenses' => PricingExpense::oneFormattedForSelect($model, 'other-direct-manpower-expense'),
            'otherDirectOperationsExpenses' => PricingExpense::oneFormattedForSelect($model, 'other-direct-operations-expense'),
            'salesAndMarketExpenses' => PricingExpense::oneFormattedForSelect($model, 'sales-and-market-expense'),
            'generalExpenses' => PricingExpense::oneFormattedForSelect($model, 'general-and-administrative-expense'),
            'currencies' => App(CurrencyRepository::class)->allFormattedForSelect($model),

        ];
    }

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
            'revenueBusinessLines' => App(RevenueBusinessLineRepository::class)->allFormattedForSelect($currentCompanyId),
            'serviceCategories' => App(ServiceCategoryRepository::class)->allFormattedForSelect($currentCompanyId),
            'serviceItems' => App(ServiceItemRepository::class)->allFormattedForSelect($currentCompanyId),
            'serviceNatures' => App(ServiceNatureRepository::class)->allFormattedForSelect($currentCompanyId),
            'pricingPlans' => PricingPlan::allFormattedForSelect($currentCompanyId),
            'directManpowerExpensePositions' => App(PositionRepository::class)->allFormattedForSelect('direct-manpower-expense'),
            'freelancerExpensePositions' => App(PositionRepository::class)->allFormattedForSelect('freelancer-expenses'),
            'otherVariableManpowerExpenses' => PricingExpense::allFormattedForSelect('other-direct-manpower-expense', $currentCompanyId),
            'otherDirectOperationsExpenses' => PricingExpense::allFormattedForSelect('other-direct-operations-expense', $currentCompanyId),
            'salesAndMarketExpenses' => PricingExpense::allFormattedForSelect('sales-and-market-expense', $currentCompanyId),
            'generalExpenses' => PricingExpense::allFormattedForSelect('general-and-administrative-expense', $currentCompanyId),
            'currencies' => App(CurrencyRepository::class)->allFormattedForSelect($currentCompanyId),
            'redirectAfterSubmitRoute' => route('admin.view.quick.pricing.calculator', ['company' => $currentCompanyId, 'active' => 'quick-price-calculator']),
            'type' => 'create',
			'customers'=>HHelpers::formatForSelect2(Partner::where('company_id',$currentCompanyId)->onlyCustomers()->get()->pluck('name','id')->toArray())
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
