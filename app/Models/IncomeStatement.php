<?php

namespace App\Models;

use App\Helpers\HArr;
use App\Helpers\HVero;
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

/**
 * @property int $id
 * @property int $can_view_actual_report
 * @property int|null $is_caching_modified
 * @property int|null $is_caching_adjusted
 * @property int|null $is_caching_actual
 * @property int|null $is_caching_forecast
 * @property string $name
 * @property string $duration
 * @property string|null $type
 * @property string $duration_type
 * @property string $start_from
 * @property int $company_id
 * @property int|null $creator_id
 * @property int|null $financial_statement_id
 * @property string|null $cash_and_banks_beginning_balance
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $entered_receivables_and_payments_table
 * @property-read \App\Models\FinancialStatement|null $FinancialStatement
 * @property-read \App\Models\Company $company
 * @property-read \App\Models\User|null $creator
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\IncomeStatementItem> $mainItems
 * @property-read int|null $main_items_count
 * @property-read bool|null $main_items_exists
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\IncomeStatementItem> $mainRows
 * @property-read int|null $main_rows_count
 * @property-read bool|null $main_rows_exists
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\IncomeStatementItem> $subItems
 * @property-read int|null $sub_items_count
 * @property-read bool|null $sub_items_exists
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\IncomeStatement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\IncomeStatement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\IncomeStatement onlyCurrentCompany(?int $companyId = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\IncomeStatement query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\IncomeStatement whereCanViewActualReport($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\IncomeStatement whereCashAndBanksBeginningBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\IncomeStatement whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\IncomeStatement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\IncomeStatement whereCreatorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\IncomeStatement whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\IncomeStatement whereDurationType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\IncomeStatement whereEnteredReceivablesAndPaymentsTable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\IncomeStatement whereFinancialStatementId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\IncomeStatement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\IncomeStatement whereIsCachingActual($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\IncomeStatement whereIsCachingAdjusted($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\IncomeStatement whereIsCachingForecast($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\IncomeStatement whereIsCachingModified($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\IncomeStatement whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\IncomeStatement whereStartFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\IncomeStatement whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\IncomeStatement whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class IncomeStatement extends Model implements  IHaveAllRelations, IExportable, IFinancialStatementAble
{
	use  IncomeStatementAccessor, IncomeStatementMutator, IncomeStatementRelation, CompanyScope;


	protected $guarded = [
		'id'
	];
	
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

	// public static function getCrudViewName(): string
	// {
	// 	return 'admin.income-statement.create';
	// }

	// public static function getViewVars(): array
	// {
	// 	$currentCompanyId =  getCurrentCompanyId();

	// 	return [
	// 		'getDataRoute' => route('admin.get.income.statement', ['company' => $currentCompanyId]),
	// 		'modelName' => 'IncomeStatement',
	// 		'exportRoute' => route('admin.export.income.statement', $currentCompanyId),
	// 		'createRoute' => route('admin.create.income.statement', $currentCompanyId),
	// 		'storeRoute' => route('admin.store.income.statement', $currentCompanyId),
	// 		'hasChildRows' => false,
	// 		'pageTitle' => IncomeStatement::getPageTitle(),
	// 		'type' => 'create',
	// 		'company' => Company::find($currentCompanyId),
	// 		'redirectAfterSubmitRoute' => route('admin.view.income.statement', ['company' => getCurrentCompanyId()]),
	// 		'durationTypes' => HVero::getDurationIntervalTypesForSelect()
	// 	];
	// }
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
			// 'redirectAfterSubmitRoute' => route('admin.view.income.statement', $currentCompanyId),
			'type' => 'create',
			'incomeStatement' => $incomeStatement,
			// 'cashFlowStatement' => $options['cashFlowStatement'],
			'interval' => HVero::getIntervalForSelect($options['incomeStatement']->getDurationType()),
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
