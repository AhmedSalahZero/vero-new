<?php

namespace App\Models\Repositories;

use App\Interfaces\Repositories\IBaseRepository;
use App\Jobs\RecalculateIncomeStatementCalculationForTypesJob;
use App\Models\CashFlowStatement;
use App\Models\Company;
use App\Models\FinancialStatement;
use App\Models\FinancialStatementItem;
use App\Models\IncomeStatement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinancialStatementRepository implements IBaseRepository
{

	public function store(Request $request)
	{

		$financialStatement = new FinancialStatement();

		$incomeStatement = new IncomeStatement();

		$cashFlowStatement = new CashFlowStatement();



		$financialStatementName = $request->name;

		$financialStatement = $financialStatement->storeMainSection($request)->storeMainItems($request);

		$request['financial_statement_id'] = $financialStatement->id;

		$request['name'] = $financialStatementName . ' Income Statement';
		$incomeStatement = $incomeStatement->storeMainSection($request)->storeMainItems($request);

		$request['name'] = $financialStatementName . ' Cash Flow Statement';
		$cashFlowStatement = $cashFlowStatement->storeMainSection($request)->storeMainItems($request);

		$request['name'] = $financialStatementName . ' Balance Sheet';


		return $financialStatement;
	}



	public function update( $financialStatement, Request $request): void
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

		$filterData = $this->commonScope($request);

		$allFilterDataCounter = $filterData->count();

		$datePerPage = $filterData->skip(Request('start'))->take(Request('length'))->get()->each(function (FinancialStatement $financialStatement, $index) {
			$financialStatement->cash_flow_statement_id = $financialStatement->cashFlowStatement ? $financialStatement->cashFlowStatement->id : 0;
			$financialStatement->income_statement_id = $financialStatement->incomeStatement ? $financialStatement->incomeStatement->id : 0;
			$financialStatement->order = $index + 1;
			$financialStatement->can_view_income_statement_actual_report = $financialStatement->incomeStatement ? $financialStatement->incomeStatement->can_view_actual_report : false;
			$financialStatement->can_view_cash_flow_statement_actual_report = false;
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

	// public function paginateReport(Request $request, FinancialStatement $financialStatement): array
	// {

	// 	$filterData = $this->commonScopeForReport($request, $financialStatement);

	// 	$allFilterDataCounter = $filterData->count();

	// 	$dataWithRelations = collect([]);
	// 	$datePerPage = $filterData->get()->each(function (FinancialStatementItem $financialStatementItem, $index) use ($dataWithRelations, $financialStatement, $request) {
	// 		$dataWithRelations->add($financialStatementItem);
	// 		$financialStatementItem->getSubItems($financialStatement->id, $request->get('sub_item_type'), $request->get('sub_item_name'))->each(function ($subItem) use ($dataWithRelations, $financialStatementItem) {
	// 			$subItem->isSubItem = true; // isSubRow

	// 			if ($financialStatementItem->has_depreciation_or_amortization) {
	// 				$subItem->pivot->can_be_depreciation = true;
	// 			}
	// 			$dataWithRelations->add($subItem);
	// 		});
	// 	});


	// 	return [
	// 		'data' => $dataWithRelations,
	// 		"draw" => (int)Request('draw'),
	// 		"recordsTotal" => FinancialStatementItem::count(),
	// 		"recordsFiltered" => $allFilterDataCounter,
	// 	];
	// }
	public function commonScope(Request $request): builder
	{
		return FinancialStatement::onlyCurrentCompany()
		->with(['incomeStatement','cashFlowStatement'])
		->when($request->filled('search_input'), function (Builder $builder) use ($request) {

			$builder
				->where(function (Builder $builder) use ($request) {
					$builder->when($request->filled('search_input'), function (Builder $builder) use ($request) {
						$keyword = "%" . $request->get('search_input') . "%";
					
					});
				});
		})
			->orderBy('financial_statements.' . getDefaultOrderBy()['column'], getDefaultOrderBy()['direction']);
	}

	// public function commonScopeForReport(Request $request, FinancialStatement $financialStatement): builder
	// {

	// 	return FinancialStatementItem::with(['subItems' => function ($builder) use ($financialStatement) {
	// 		$builder->where('financial_statement_id', $financialStatement->id);
	// 		//		->where('is_quantity', 0)
	// 	}])->whereHas('financialStatements', function (Builder $builder) use ($financialStatement) {
	// 		$builder->where('financial_statements.id', $financialStatement->id);
	// 	})
	// 		->orderBy('financial_statement_items.id', 'asc');
	// }
}
