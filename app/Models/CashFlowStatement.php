<?php

namespace App\Models;

use App\Interfaces\Models\IBaseModel;
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

class  CashFlowStatement extends Model implements IBaseModel, IHaveAllRelations, IExportable, IShareable, IFinancialStatementAble
{
	use CashFlowStatementAccessor,
		CashFlowStatementMutator,
		CashFlowStatementRelation,
		CompanyScope;


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
			// ->orderBy('ordered','asc');
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
			'redirectAfterSubmitRoute' => route('admin.view.cash.flow.statement', $currentCompanyId),
			'type' => 'create',
			'company' => Company::find($currentCompanyId),
			'redirectAfterSubmitRoute' => route('admin.view.cash.flow.statement', ['company' => getCurrentCompanyId()]),
			'durationTypes' => getDurationIntervalTypesForSelect()
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
			'redirectAfterSubmitRoute' => route('admin.view.cash.flow.statement', $currentCompanyId),
			'type' => 'create',
			'cashFlowStatement' => $options['cashFlowStatement'],
			'interval' => getIntervalForSelect($options['cashFlowStatement']->getDurationType()),
			'reportType' => $options['reportType'],
			'dependsRelation' => getDependsMaps($options['financial_statement_able_id'], new static),
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
