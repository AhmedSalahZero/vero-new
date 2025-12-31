<?php

namespace App\Models;

use App\Interfaces\Models\IBaseModel;
use App\Interfaces\Models\IExportable;
use App\Interfaces\Models\IHaveAllRelations;
use App\Interfaces\Models\Interfaces\IFinancialStatementAble;
use App\Interfaces\Models\IShareable;
use App\Models\Traits\Accessors\BalanceSheetAccessor;
use App\Models\Traits\Mutators\BalanceSheetMutator;
use App\Models\Traits\Relations\BalanceSheetRelation;
use App\Models\Traits\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class  BalanceSheet extends Model implements IBaseModel, IHaveAllRelations, IExportable, IShareable, IFinancialStatementAble
{
	use  BalanceSheetAccessor, BalanceSheetMutator, BalanceSheetRelation, CompanyScope;


	protected $guarded = [
		'id'
	];
	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'financial_statement_ables';

	public static function getShareableEditViewVars($model): array
	{
		return [
			'pageTitle' => BalanceSheet::getPageTitle(),
		];
	}

	public function getRouteKeyName()
	{
		return 'financial_statement_ables.id';
	}
	public static function exportViewName(): string
	{
		return __('Income Statement');
	}
	public static function getFileName(): string
	{
		return __('Income Statement');
	}

	protected static function booted()
	{		parent::boot();
		static::addGlobalScope(function (Builder $builder) {
			$builder->where('type', 'BalanceSheet');
		});
		
		static::deleting(function(self $balanceSheet) { 
			DB::table('financial_statement_able_main_item_sub_items')->where('financial_statement_able_id',$balanceSheet->id)->delete();
			DB::table('financial_statement_able_item_main_item')->where('financial_statement_able_id',$balanceSheet->id)->delete();
		}); 
		// static::addGlobalScope(new StateCountryScope);
	}

	public static function getCrudViewName(): string
	{
		return 'admin.balance-sheet.create';
	}

	public static function getViewVars(): array
	{
		$currentCompanyId =  getCurrentCompanyId();

		return [
			'getDataRoute' => route('admin.get.balance.sheet', ['company' => $currentCompanyId]),
			'modelName' => 'BalanceSheet',
			'exportRoute' => route('admin.export.balance.sheet', $currentCompanyId),
			'createRoute' => route('admin.create.balance.sheet', $currentCompanyId),
			'storeRoute' => route('admin.store.balance.sheet', $currentCompanyId),
			'hasChildRows' => false,
			'pageTitle' => BalanceSheet::getPageTitle(),
			'redirectAfterSubmitRoute' => route('admin.view.balance.sheet', $currentCompanyId),
			'type' => 'create',
			'company' => Company::find($currentCompanyId),
			'redirectAfterSubmitRoute' => route('admin.view.balance.sheet', ['company' => getCurrentCompanyId()]),
			'durationTypes' => getDurationIntervalTypesForSelect()
		];
	}
	public static function getReportViewVars(array $options = []): array
	{

		$currentCompanyId =  getCurrentCompanyId();
		$reportType = $options['reportType'];
		return [
			'getDataRoute' => route('admin.get.balance.sheet.report', ['company' => $currentCompanyId, 'balanceSheet' => $options['financial_statement_able_id']]),
			'modelName' => 'BalanceSheetReport',
			'exportRoute' => route('admin.export.balance.sheet.report', $currentCompanyId),
			'createRoute' => route('admin.create.balance.sheet.' . $reportType . '.report', [
				'company' => $currentCompanyId,
				'balanceSheet' => $options['financial_statement_able_id']
			]),
			'storeRoute' => route('admin.store.balance.sheet.report', $currentCompanyId),
			'hasChildRows' => false,
			'pageTitle' => __('Income Statement Report'),
			'redirectAfterSubmitRoute' => route('admin.view.balance.sheet', $currentCompanyId),
			'type' => 'create',
			'balanceSheet' => $options['balanceSheet'],
			'interval' => getIntervalForSelect($options['balanceSheet']->getDurationType()),
			'reportType' => $reportType = $options['reportType'],
			'dependsRelation' => getDependsMaps($options['financial_statement_able_id'], new static)
		];
	}
	public static function getPageTitle(): string
	{
		return __('Income Statement');
	}

	public function getAllRelationsNames(): array
	{
		return [
			// 'revenueBusinessLine',
			// 'serviceCategory','serviceItem','serviceNatureRelation','currency','otherVariableManpowerExpenses',
			// 'directManpowerExpenses','salesAndMarketingExpenses','otherDirectOperationExpenses','generalExpenses','freelancerExpensePositions',
			// 'directManpowerExpensePositions','freelancerExpenses','profitability'
		];
	}
}
