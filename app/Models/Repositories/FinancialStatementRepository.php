<?php

namespace App\Models\Repositories;

use App\Interfaces\Models\IBaseModel;
use App\Interfaces\Repositories\IBaseRepository;
use App\Jobs\RecalculateIncomeStatementCalculationForTypesJob;
use App\Models\BalanceSheet;
use App\Models\CashFlowStatement;
use App\Models\Company;
use App\Models\FinancialStatement;
use App\Models\FinancialStatementItem;
use App\Models\IncomeStatement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinancialStatementRepository implements IBaseRepository
{

	public function all(): Collection
	{
		return FinancialStatement::withAllRelations()->onlyCurrentCompany()->get();
	}

	public function allFormatted(): array
	{
		return FinancialStatement::onlyCurrentCompany()->get()->pluck('name', 'id')->toArray();
	}
	public function allFormattedForSelect()
	{
		$financialStatements = $this->all();
		return formatOptionsForSelect($financialStatements, 'getId', 'getName');
	}

	public function getAllExcept($id): ?Collection
	{
		return FinancialStatement::onlyCurrentCompany()->where('id', '!=', $id)->get();
	}

	public function query(): Builder
	{
		return FinancialStatement::onlyCurrentCompany()->query();
	}
	public function Random(): Builder
	{
		return FinancialStatement::onlyCurrentCompany()->inRandomOrder();
	}

	public function find(?int $id): FinancialStatement
	{
		return FinancialStatement::onlyCurrentCompany()->find($id);
	}

	public function getLatest($column = 'id'): ?FinancialStatement
	{
		return FinancialStatement::onlyCurrentCompany()->latest($column)->first();
	}
	public function store(Request $request): IBaseModel
	{

		$financialStatement = new FinancialStatement();

		$incomeStatement = new IncomeStatement();

		$cashFlowStatement = new CashFlowStatement();

		$balanceSheet = new BalanceSheet();

		$financialStatementName = $request->name;

		$financialStatement = $financialStatement->storeMainSection($request)->storeMainItems($request);

		$request['financial_statement_id'] = $financialStatement->id;

		$request['name'] = $financialStatementName . ' Income Statement';
		$incomeStatement = $incomeStatement->storeMainSection($request)->storeMainItems($request);

		$request['name'] = $financialStatementName . ' Cash Flow Statement';
		$cashFlowStatement = $cashFlowStatement->storeMainSection($request)->storeMainItems($request);

		$request['name'] = $financialStatementName . ' Balance Sheet';
		$balanceSheet = $balanceSheet->storeMainSection($request)->storeMainItems($request);


		return $financialStatement;
	}

	public function storeReport(Request $request): IBaseModel
	{
		$financialStatement = new FinancialStatement();

		$financialStatement->storeReport($request);

		return $financialStatement;
	}

	public function update(IBaseModel $financialStatement, Request $request): void
	{
		/**
		 * @var FinancialStatement $financialStatement
		 */

		$financialStatement->storeMainSection($request);
		$financialStatement->updateIndexedDates();
		$incomeStatement = $financialStatement->incomeStatement;
			// هنا هنحدث ال corporate taxes للعناصر الصب علشان اعادة الحسبة
			DB::table('financial_statement_able_main_item_sub_items')->where('financial_statement_able_id',$incomeStatement->id)->where('financial_statement_able_item_id',FinancialStatementItem::CORPORATE_TAXES_ID)->update([
				'percentage_value'=>$request->get('corporate_taxes_rate',0)
			]);
	
		foreach(getAllFinancialAbleTypes() as $index => $subItemType){
		
			$incomeStatement->refreshCalculationFor($subItemType);
			
		}
		
		
		
	}
	public function formatSelectFor(string $selectedValue): string
	{
		$select = '<select name="selected_interval" class="select select2">';
		$interval = [
			'monthly' => __('Monthly'),
			'quarterly' => __('Quarterly'),
			'semi-annually' => __('Semi Annually'),
			'annually' => __('Annually')
		];
		foreach ($interval as $duration => $durationTranslated) {
			if ($duration == $selectedValue) {
				$select .= ' <option selected value="' . $duration . '">' . $durationTranslated . '</option>  ';
			} else {
				$select .= ' <option value="' . $duration . '">' . $durationTranslated . '</option>  ';
			}
		}
		$select .= "</select>";
		return $select;
	}
	public function paginate(Request $request): array
	{
		$start = microtime(true);

		$filterData = $this->commonScope($request);

		$allFilterDataCounter = $filterData->count();

		$datePerPage = $filterData->skip(Request('start'))->take(Request('length'))->get()->each(function (FinancialStatement $financialStatement, $index) {
			$financialStatement->creator_name = $financialStatement->getCreatorName();
			$financialStatement->cash_flow_statement_id = $financialStatement->cashFlowStatement ? $financialStatement->cashFlowStatement->id : 0;
			// $financialStatement->balance_sheet_id = $financialStatement->balanceSheet ? $financialStatement->balanceSheet->id : 0;
			$financialStatement->income_statement_id = $financialStatement->incomeStatement ? $financialStatement->incomeStatement->id : 0;
			$financialStatement->created_at_formatted = formatDateFromString($financialStatement->created_at);
			$financialStatement->updated_at_formatted = formatDateFromString($financialStatement->updated_at);
			$financialStatement->order = $index + 1;
			$financialStatement->can_view_income_statement_actual_report = $financialStatement->incomeStatement ? $financialStatement->incomeStatement->can_view_actual_report : false;

			// $financialStatement->can_view_balance_sheet_actual_report = $financialStatement->balanceSheet ? $financialStatement->balanceSheet->canViewActualReport() : false;
			
			$financialStatement->can_view_cash_flow_statement_actual_report = false;
			// $financialStatement->can_view_cash_flow_statement_actual_report = $financialStatement->cashFlowStatement ? $financialStatement->cashFlowStatement->canViewActualReport() : false;
			$financialStatement->duration_type_select = $this->formatSelectFor($financialStatement->duration_type);
			$financialStatement->can_edit_duration_type = $financialStatement->canEditDurationType();
		});
		return [
			'data' => $datePerPage,
			"draw" => (int)Request('draw'),
			"recordsTotal" => FinancialStatement::onlyCurrentCompany()->count(),
			"recordsFiltered" => $allFilterDataCounter,
		];
	}

	public function paginateReport(Request $request, FinancialStatement $financialStatement): array
	{

		$filterData = $this->commonScopeForReport($request, $financialStatement);

		$allFilterDataCounter = $filterData->count();

		$dataWithRelations = collect([]);
		$datePerPage = $filterData->get()->each(function (FinancialStatementItem $financialStatementItem, $index) use ($dataWithRelations, $financialStatement, $request) {
			$financialStatementItem->creator_name = $financialStatementItem->getCreatorName();
			$financialStatementItem->created_at_formatted = formatDateFromString($financialStatementItem->created_at);
			$financialStatementItem->updated_at_formatted = formatDateFromString($financialStatementItem->updated_at);
			$financialStatementItem->order = $index + 1;

			$dataWithRelations->add($financialStatementItem);
			$financialStatementItem->getSubItems($financialStatement->id, $request->get('sub_item_type'), $request->get('sub_item_name'))->each(function ($subItem) use ($dataWithRelations, $financialStatementItem) {
				$subItem->isSubItem = true; // isSubRow

				if ($financialStatementItem->has_depreciation_or_amortization) {
					$subItem->pivot->can_be_depreciation = true;
				}
				$dataWithRelations->add($subItem);
			});
		});


		return [
			'data' => $dataWithRelations,
			"draw" => (int)Request('draw'),
			"recordsTotal" => FinancialStatementItem::count(),
			"recordsFiltered" => $allFilterDataCounter,
		];
	}
	public function commonScope(Request $request): builder
	{
		return FinancialStatement::onlyCurrentCompany()
		->with(['incomeStatement','cashFlowStatement'])
		->when($request->filled('search_input'), function (Builder $builder) use ($request) {

			$builder
				->where(function (Builder $builder) use ($request) {
					$builder->when($request->filled('search_input'), function (Builder $builder) use ($request) {
						$keyword = "%" . $request->get('search_input') . "%";
						$builder;
					});
				});
		})
			->orderBy('financial_statements.' . getDefaultOrderBy()['column'], getDefaultOrderBy()['direction']);
	}

	public function commonScopeForReport(Request $request, FinancialStatement $financialStatement): builder
	{

		return FinancialStatementItem::with(['subItems' => function ($builder) use ($financialStatement) {
			$builder->where('financial_statement_id', $financialStatement->id);
			//		->where('is_quantity', 0)
		}])->whereHas('financialStatements', function (Builder $builder) use ($financialStatement) {
			$builder->where('financial_statements.id', $financialStatement->id);
		})
			->orderBy('financial_statement_items.id', 'asc');
	}
}
