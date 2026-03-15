<?php

namespace App\Models;

use App\Helpers\HVero;
use App\Interfaces\Models\IExportable;
use App\Interfaces\Models\IHaveAllRelations;
use App\Interfaces\Models\IShareable;
use App\Models\Traits\Accessors\FinancialStatementAccessor;
use App\Models\Traits\Mutators\FinancialStatementMutator;
use App\Models\Traits\Relations\FinancialStatementRelation;
use App\Models\Traits\Scopes\CompanyScope;
use App\Models\Traits\Scopes\withAllRelationsScope;
use App\Traits\HasIndexedDates;
use Illuminate\Database\Eloquent\Model;
/**
 * App\Models\FinancialStatement
 *
 * @property int $id
 * @property string $name
 * @property string $type
 * @property string $duration
 * @property string $duration_type
 * @property string $start_from
 * @property int $company_id
 * @property int|null $creator_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property float $corporate_taxes_rate
 * @property float $salary_taxes_rate
 * @property float $social_insurance_rate
 * @property string|null $study_start_date
 * @property int $duration_in_years
 * @property string|null $study_end_date
 * @property array|null $study_dates
 * @property array|null $operation_dates
 * @property float|null $operation_start_month
 * @property string|null $operation_start_date
 * @property string $financial_year_start_month
 * @property-read \App\Models\Company $company
 * @property-read \App\Models\User|null $creator
 * @property-read \App\Models\IncomeStatement|null $incomeStatement
 * @property-read \App\Models\CashFlowStatement|null $cashFlowStatement
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FinancialStatementItem> $mainItems
 * @property-read int|null $main_items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FinancialStatementItem> $subItems
 * @property-read int|null $sub_items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FinancialStatementItem> $mainRows
 * @property-read int|null $main_rows_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SharingLink> $sharingLinks
 * @property-read int|null $sharing_links_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialStatement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialStatement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialStatement query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialStatement onlyCurrentCompany(?int $companyId = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialStatement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialStatement whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialStatement whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialStatement whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialStatement whereDurationType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialStatement whereStartFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialStatement whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialStatement whereCreatorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialStatement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialStatement whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialStatement whereCorporateTaxesRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialStatement whereSalaryTaxesRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialStatement whereSocialInsuranceRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialStatement whereStudyStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialStatement whereDurationInYears($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialStatement whereStudyEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialStatement whereStudyDates($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialStatement whereOperationDates($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialStatement whereOperationStartMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialStatement whereOperationStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialStatement whereFinancialYearStartMonth($value)
 * @property-read bool|null $main_items_exists
 * @property-read bool|null $main_rows_exists
 * @property-read bool|null $sharing_links_exists
 * @property-read bool|null $sub_items_exists
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\FinancialStatement withAllRelations(?int $companyId = null)
 * @mixin \Eloquent
 */
class FinancialStatement extends Model implements  IHaveAllRelations, IExportable, IShareable
{
	use HasIndexedDates, FinancialStatementAccessor, FinancialStatementMutator, FinancialStatementRelation, CompanyScope, withAllRelationsScope;
	protected  $casts = [
		
		'operation_dates'=>'array',
		'study_dates'=>'array'
	];	
	protected $guarded = [
		'id'
	];
	
	public static function getShareableEditViewVars($model): array
	{

		return [
			'pageTitle' => FinancialStatement::getPageTitle(),
		];
	}

	public function getRouteKeyName()
	{
		return 'financial_statements.id';
	}
	public static function exportViewName(): string
	{
		return __('Financial Statement');
	}
	public static function getFileName(): string
	{
		return __('Financial Statement');
	}


	public static function getCrudViewName(): string
	{
		return 'admin.financial-statement.create';
	}

	public static function getViewVars(): array
	{
		$currentCompanyId =  getCurrentCompanyId();
		return [
			'getDataRoute' => route('admin.get.financial.statement', ['company' => $currentCompanyId]),
			'modelName' => 'FinancialStatement',
			'exportRoute' => route('admin.export.financial.statement', $currentCompanyId),
			'createRoute' => route('admin.create.financial.statement', $currentCompanyId),
			'storeRoute' => route('admin.store.financial.statement', $currentCompanyId),
			'hasChildRows' => false,
			'pageTitle' => FinancialStatement::getPageTitle(),
			'type' => 'create',
			'company' => Company::find($currentCompanyId),
			'redirectAfterSubmitRoute' => route('admin.view.financial.statement', ['company' => getCurrentCompanyId()]),
			'durationTypes' => HVero::getDurationIntervalTypesForSelect()
		];
	}
	public static function getReportViewVars(array $options = []): array
	{

		$currentCompanyId =  getCurrentCompanyId();

		return [
			'getDataRoute' => route('admin.get.financial.statement.report', ['company' => $currentCompanyId, 'financialStatement' => $options['financial_statement_id']]),
			'modelName' => 'FinancialStatementReport',
			'exportRoute' => route('admin.export.financial.statement.report', $currentCompanyId),
			'createRoute' => route('admin.create.financial.statement.report', [
				'company' => $currentCompanyId,
				'financialStatement' => $options['financial_statement_id']
			]),
			'storeRoute' => route('admin.store.financial.statement.report', $currentCompanyId),
			'hasChildRows' => false,
			'pageTitle' => __('Financial Statement Report'),
			'redirectAfterSubmitRoute' => route('admin.view.financial.statement', $currentCompanyId),
			'type' => 'create',
			'financialStatement' => $options['financialStatement'],
			'interval' => HVero::getIntervalForSelect($options['financialStatement']->getDurationType()),
		];
	}
	public static function getPageTitle(): string
	{
		return __('Income Statement Planning / Actual');
	}

	public function getAllRelationsNames(): array
	{
		return [
		
		];
	}
}
