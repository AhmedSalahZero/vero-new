<?php

namespace App\Models;

use App\Helpers\HVero;
use App\Interfaces\Models\IExportable;
use App\Interfaces\Models\IHaveAllRelations;
use App\Interfaces\Models\Interfaces\IFinancialStatementAble;
use App\Interfaces\Models\IShareable;
use App\Models\Traits\Accessors\CashFlowStatementAccessor;
use App\Models\Traits\Mutators\CashFlowStatementMutator;
use App\Models\Traits\Relations\CashFlowStatementRelation;
use App\Models\Traits\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * App\Models\CashFlowStatement
 *
 * @property int $id
 * @property int $can_view_actual_report
 * @property bool|null $is_caching_modified
 * @property bool|null $is_caching_adjusted
 * @property bool|null $is_caching_actual
 * @property bool|null $is_caching_forecast
 * @property string $name
 * @property string $duration
 * @property string|null $type
 * @property string $duration_type
 * @property string $start_from
 * @property int $company_id
 * @property int|null $creator_id
 * @property int|null $financial_statement_id
 * @property string|null $cash_and_banks_beginning_balance
 * @property string|null $entered_receivables_and_payments_table
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ReceivableAndPayment> $receivables_and_payments
 * @method static \Illuminate\Database\Eloquent\Builder<static> whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static> whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static> whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static> whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static> whereDurationType($value)
 * @property-read \App\Models\FinancialStatement|null $FinancialStatement
 * @property-read \App\Models\Company $company
 * @property-read \App\Models\User|null $creator
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CashFlowStatementItem> $mainItems
 * @property-read int|null $main_items_count
 * @property-read bool|null $main_items_exists
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CashFlowStatementItem> $mainRows
 * @property-read int|null $main_rows_count
 * @property-read bool|null $main_rows_exists
 * @property-read int|null $receivables_and_payments_count
 * @property-read bool|null $receivables_and_payments_exists
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CashFlowStatementItem> $subItems
 * @property-read int|null $sub_items_count
 * @property-read bool|null $sub_items_exists
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CashFlowStatement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CashFlowStatement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CashFlowStatement onlyCurrentCompany(?int $companyId = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CashFlowStatement query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CashFlowStatement whereCanViewActualReport($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CashFlowStatement whereCashAndBanksBeginningBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CashFlowStatement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CashFlowStatement whereCreatorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CashFlowStatement whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CashFlowStatement whereEnteredReceivablesAndPaymentsTable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CashFlowStatement whereFinancialStatementId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CashFlowStatement whereIsCachingActual($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CashFlowStatement whereIsCachingAdjusted($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CashFlowStatement whereIsCachingForecast($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CashFlowStatement whereIsCachingModified($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CashFlowStatement whereStartFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|\App\Models\CashFlowStatement whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CashFlowStatement extends Model implements  IHaveAllRelations, IExportable, IShareable, IFinancialStatementAble
{
	use CashFlowStatementAccessor,
		CashFlowStatementMutator,
		CashFlowStatementRelation,
		CompanyScope;


	protected $guarded = [
		'id'
	];
	
	protected $table = 'financial_statement_ables';

	public static function getShareableEditViewVars($model): array
	{
		return [
			'pageTitle' => CashFlowStatement::getPageTitle(),
		];
	}

	public function getRouteKeyName()
	{
		return 'financial_statement_ables.id';
	}
	public static function exportViewName(): string
	{
		return __('Cash Flow Statement');
	}
	public static function getFileName(): string
	{
		return __('Cash Flow Statement');
	}

	protected static function booted()
	{
		static::addGlobalScope(function (Builder $builder) {
			$builder->where('type', 'CashFlowStatement');
		
		});
		static::deleting(function(self $cashFlowStatement) { 
			DB::table('financial_statement_able_main_item_sub_items')->where('financial_statement_able_id',$cashFlowStatement->id)->delete();
			DB::table('financial_statement_able_item_main_item')->where('financial_statement_able_id',$cashFlowStatement->id)->delete();
		}); 
	}

	public static function getCrudViewName(): string
	{
		return 'admin.cash-flow-statement.create';
	}

	public static function getViewVars(): array
	{
		$currentCompanyId =  getCurrentCompanyId();

		return [
			'getDataRoute' => route('admin.get.cash.flow.statement', ['company' => $currentCompanyId]),
			'modelName' => 'CashFlowStatement',
			'exportRoute' => route('admin.export.cash.flow.statement', $currentCompanyId),
			'createRoute' => route('admin.create.cash.flow.statement', $currentCompanyId),
			'storeRoute' => route('admin.store.cash.flow.statement', $currentCompanyId),
			'hasChildRows' => false,
			'pageTitle' => CashFlowStatement::getPageTitle(),
			// 'redirectAfterSubmitRoute' => route('admin.view.cash.flow.statement', $currentCompanyId),
			'type' => 'create',
			'company' => Company::find($currentCompanyId),
			// 'redirectAfterSubmitRoute' => route('admin.view.cash.flow.statement', ['company' => getCurrentCompanyId()]),
			'durationTypes' => HVero::getDurationIntervalTypesForSelect()
		];
	}
	public static function getReportViewVars(array $options = []): array
	{

		$currentCompanyId =  getCurrentCompanyId();
		$reportType = $options['reportType'];

		return [
			'getDataRoute' => route('admin.get.cash.flow.statement.report', ['company' => $currentCompanyId, 'cashFlowStatement' => $options['financial_statement_able_id']]),
			'modelName' => 'CashFlowStatementReport',
			'exportRoute' => route('admin.export.cash.flow.statement.report', $currentCompanyId),
			'createRoute' => route('admin.create.cash.flow.statement.' . $reportType . '.report', [
				'company' => $currentCompanyId,
				'cashFlowStatement' => $options['financial_statement_able_id']
			]),
			'storeRoute' => route('admin.store.cash.flow.statement.report', $currentCompanyId),
			'hasChildRows' => false,
			'pageTitle' => __('Cash Flow Statement Report'),
			// 'redirectAfterSubmitRoute' => route('admin.view.cash.flow.statement', $currentCompanyId),
			'type' => 'create',
			'cashFlowStatement' => $options['cashFlowStatement'],
			'interval' => HVero::getIntervalForSelect($options['cashFlowStatement']->getDurationType()),
			'reportType' => $options['reportType'],
			'dependsRelation' => getDependsMaps($options['financial_statement_able_id'], new self),
			'cashes'=>$options['cashes']
		];
	}
	public static function getPageTitle(): string
	{
		return __('Cash Flow Statement');
	}

	public function getAllRelationsNames(): array
	{
		return [];
	}
	public function receivables_and_payments()
	{
		return $this->hasMany(ReceivableAndPayment::class , 'cash_flow_statement_id','id');
	}
	public function getCashAndBanksBeginningBalance()
	{
		return $this->cash_and_banks_beginning_balance ?:0;
	}
	

}
