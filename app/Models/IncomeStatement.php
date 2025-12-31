<?php

namespace App\Models;

use App\Helpers\HArr;
use App\Interfaces\Models\IBaseModel;
use App\Interfaces\Models\IExportable;
use App\Interfaces\Models\IHaveAllRelations;
use App\Interfaces\Models\Interfaces\IFinancialStatementAble;
use App\Interfaces\Models\IShareable;
use App\Models\Traits\Accessors\IncomeStatementAccessor;
use App\Models\Traits\Mutators\IncomeStatementMutator;
use App\Models\Traits\Relations\IncomeStatementRelation;
use App\Models\Traits\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class  IncomeStatement extends Model implements IBaseModel, IHaveAllRelations, IExportable, IShareable, IFinancialStatementAble
{
	use  IncomeStatementAccessor, IncomeStatementMutator, IncomeStatementRelation, CompanyScope;


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
			'pageTitle' => IncomeStatement::getPageTitle(),
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
	{
		parent::boot();
		static::addGlobalScope(function (Builder $builder) {
			$builder->where('type', 'IncomeStatement');
		});
		static::deleting(function(self $incomeStatement) { 
			DB::table('financial_statement_able_main_item_sub_items')->where('financial_statement_able_id',$incomeStatement->id)->delete();
			DB::table('financial_statement_able_item_main_item')->where('financial_statement_able_id',$incomeStatement->id)->delete();
		}); 
		// static::addGlobalScope(new StateCountryScope);
	}

	public static function getCrudViewName(): string
	{
		return 'admin.income-statement.create';
	}

	public static function getViewVars(): array
	{
		$currentCompanyId =  getCurrentCompanyId();

		return [
			'getDataRoute' => route('admin.get.income.statement', ['company' => $currentCompanyId]),
			'modelName' => 'IncomeStatement',
			'exportRoute' => route('admin.export.income.statement', $currentCompanyId),
			'createRoute' => route('admin.create.income.statement', $currentCompanyId),
			'storeRoute' => route('admin.store.income.statement', $currentCompanyId),
			'hasChildRows' => false,
			'pageTitle' => IncomeStatement::getPageTitle(),
			'redirectAfterSubmitRoute' => route('admin.view.income.statement', $currentCompanyId),
			'type' => 'create',
			'company' => Company::find($currentCompanyId),
			'redirectAfterSubmitRoute' => route('admin.view.income.statement', ['company' => getCurrentCompanyId()]),
			'durationTypes' => getDurationIntervalTypesForSelect()
		];
	}
	public static function getReportViewVars(array $options = []): array
	{

		$currentCompanyId =  getCurrentCompanyId();
		$reportType = $options['reportType'];
		$incomeStatement = $options['incomeStatement'];
		return [
			'getDataRoute' => route('admin.get.income.statement.report', ['company' => $currentCompanyId, 'incomeStatement' => $options['financial_statement_able_id']]),
			'modelName' => 'IncomeStatementReport',
			'exportRoute' => route('admin.export.income.statement.report',[$currentCompanyId,$incomeStatement->id,$reportType]),
			'createRoute' => route('admin.create.income.statement.' . $reportType . '.report', [
				'company' => $currentCompanyId,
				'incomeStatement' => $options['financial_statement_able_id']
			]),
			'storeRoute' => route('admin.store.income.statement.report', $currentCompanyId),
			'hasChildRows' => false,
			'pageTitle' => __('Income Statement Report'),
			'redirectAfterSubmitRoute' => route('admin.view.income.statement', $currentCompanyId),
			'type' => 'create',
			'incomeStatement' => $incomeStatement,
			// 'cashFlowStatement' => $options['cashFlowStatement'],
			'interval' => getIntervalForSelect($options['incomeStatement']->getDurationType()),
			'reportType' => $options['reportType'],
			'actualDates'=>HArr::getActualDatesAsIndexAndBoolean($incomeStatement->getIntervalFormatted())
		];
	}
	public static function getPageTitle(): string
	{
		return __('Income Statement');
	}

	public function getAllRelationsNames(): array
	{
		return [
		
		];
	}
	
	public function generateRelationDynamically(string $relationName){
		return $this->hasMany(Expense::class , 'model_id','id')->where('model_name','IncomeStatement')->where('relation_name',$relationName);
	}
}
