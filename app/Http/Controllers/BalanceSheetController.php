<?php

namespace App\Http\Controllers;

use App\Exports\BalanceSheetExport;
use App\Http\Requests\BalanceSheetRequest;
use App\Models\BalanceSheet;
use App\Models\BalanceSheetItem;
use App\Models\Company;
use App\Models\Repositories\BalanceSheetRepository;
use Illuminate\Http\Request;

class BalanceSheetController extends Controller
{
	private BalanceSheetRepository $balanceSheetRepository;

	public function __construct(BalanceSheetRepository $balanceSheetRepository)
	{
		$this->balanceSheetRepository = $balanceSheetRepository;
	}

	public function view()
	{
		return view('admin.balance-sheet.view', BalanceSheet::getViewVars());
	}
	public function create()
	{
		return view('admin.balance-sheet.create', BalanceSheet::getViewVars());
	}

	public function createReport(Company $company, BalanceSheet $balanceSheet)
	{
		return view('admin.balance-sheet.report.view', BalanceSheet::getReportViewVars([
			'financial_statement_able_id' => $balanceSheet->id, 'balanceSheet' => $balanceSheet,
			'reportType' => getReportNameFromRouteName(Request()->route()->getName())
		]));
	}

	public function paginate(Request $request)
	{
		return $this->balanceSheetRepository->paginate($request);
	}
	public function paginateReport(Request $request, Company $company, BalanceSheet $balanceSheet)
	{
		return $this->balanceSheetRepository->paginateReport($request, $balanceSheet);
	}


	public function store(BalanceSheetRequest $request)
	{

		$balanceSheet = $this->balanceSheetRepository->store($request);
		return response()->json([
			'status' => true,
			'message' => __('Balance Sheet Has Been Stored Successfully'),
			'redirectTo' => route('admin.create.balance.sheet.report', ['company' => getCurrentCompanyId(), 'balanceSheet' => $balanceSheet->id])
		]);
	}

	public function storeReport(Request $request)
	{

		$this->balanceSheetRepository->storeReport($request);

		return response()->json([
			'status' => true,
			'message' => __('Balance Sheet Has Been Stored Successfully')
		]);
	}

	public function edit(Company $company, Request $request, BalanceSheet $balanceSheet)
	{
		return view(BalanceSheet::getCrudViewName(), array_merge(BalanceSheet::getViewVars(), [
			'type' => 'edit',
			'model' => $balanceSheet
		]));
	}

	public function update(Company $company, Request $request, BalanceSheet $balanceSheet)
	{
		$this->balanceSheetRepository->update($balanceSheet, $request);
		return response()->json([
			'status' => true,
			'message' => __('Balance Sheet Has Been Updated Successfully')
		]);
	}

	public function updateReport(Company $company, Request $request)
	{
		$balanceSheetId = $request->get('financial_statement_able_id');
		$balanceSheetItemId = $request->get('financial_statement_able_item_id');
		$balanceSheet = BalanceSheet::find($balanceSheetId);
		$balanceSheet->storeReport($request);
		$balanceSheetItem = $balanceSheet->withMainItemsFor($balanceSheetItemId)->first();
		$subItemTypesToDetach = getIndexesLargerThanOrEqualIndex(getAllFinancialAbleTypes(), $request->get('sub_item_type'));
		foreach ($subItemTypesToDetach as $subItemType) {
			$percentageOrFixed = $subItemType == 'actual' ? 'non_repeating_fixed' :  $request->input('sub_items.0.percentage_or_fixed');
			$balanceSheetItem
				->withSubItemsFor($balanceSheetId, $subItemType, $request->get('sub_item_name'))
				->updateExistingPivot($balanceSheetId, [
					'sub_item_name' => html_entity_decode($request->get('new_sub_item_name')),
					'financial_statement_able_item_id' => $request->get('sub_of_id'),
					'is_depreciation_or_amortization' => $request->get('is_depreciation_or_amortization'),
					'percentage_or_fixed' => $percentageOrFixed,
					'repeating_fixed_value' => $percentageOrFixed == 'repeating_fixed' ?  $request->input('sub_items.0.repeating_fixed_value') : null,
					'percentage_value' => $percentageOrFixed == 'percentage' ?  $request->input('sub_items.0.percentage_value') : null,
					'is_percentage_of' => $percentageOrFixed == 'percentage' ? json_encode($request->input('sub_items.0.is_percentage_of')) : null,

				]);
		}


		return response()->json([
			'status' => true,
			'message' => __('Item Has Been Updated Successfully')
		]);
	}

	public function deleteReport(Company $company, Request $request)
	{

		$balanceSheetId = $request->get('financial_statement_able_id');
		$balanceSheetItemId = $request->get('financial_statement_able_item_id');
		$balanceSheet = BalanceSheet::find($balanceSheetId);
		$balanceSheet->storeReport($request);
		$balanceSheetItem = $balanceSheet->withMainItemsFor($balanceSheetItemId)->first();
		$subItemTypesToDetach = getIndexesLargerThanOrEqualIndex(getAllFinancialAbleTypes(), $request->get('sub_item_type'));
		foreach ($subItemTypesToDetach as $subItemType) {
			$balanceSheetItem->withSubItemsFor($balanceSheetId, $subItemType, $request->get('sub_item_name'))->detach($balanceSheetId);
		}
		return response()->json([
			'status' => true,
			'message' => __('Item Has Been Deleted Successfully')
		]);
	}


	public function export(Request $request)
	{
		return (new BalanceSheetExport($this->balanceSheetRepository->export($request), $request))->download();
	}
	public function exportReport(Request $request)
	{
		$formattedData = $this->formatReportDataForExport($request);
		$balanceSheetId = array_key_first($request->get('valueMainRowThatHasSubItems'));
		$balanceSheet = BalanceSheet::find($balanceSheetId);

		return (new BalanceSheetExport(collect($formattedData), $request, $balanceSheet))->download();
	}
	protected function combineMainValuesWithItsPercentageRows(array $firstItems, array $secondItems): array
	{
		$mergeArray = [];
		foreach ($firstItems as $balanceSheetId => $balanceSheetValues) {
			foreach ($balanceSheetValues as $balanceSheetItemId => $balanceSheetItemsValues) {
				foreach ($balanceSheetItemsValues as $date => $value) {
					$mergeArray[$balanceSheetId][$balanceSheetItemId][$date] = $value;
				}
			}
		}
		foreach ($secondItems as $balanceSheetId => $balanceSheetValues) {
			foreach ($balanceSheetValues as $balanceSheetItemId => $balanceSheetItemsValues) {
				foreach ($balanceSheetItemsValues as $date => $value) {
					$mergeArray[$balanceSheetId][$balanceSheetItemId][$date] = $value;
				}
			}
		}

		$mergeArray[$balanceSheetId] = orderArrayByItemsKeys($mergeArray[$balanceSheetId]);

		return $mergeArray;
	}
	public function formatReportDataForExport(Request $request)
	{
		$formattedData = [];
		$totals = $request->get('totals');
		$subTotals = $request->get('subTotals');
		$rateBalanceSheetItemsIds = BalanceSheetItem::rateFieldsIds();
		$combineMainValuesWithItsPercentageRows = $this->combineMainValuesWithItsPercentageRows($request->get('valueMainRowThatHasSubItems'), $request->get('valueMainRowWithoutSubItems'));
		foreach ($combineMainValuesWithItsPercentageRows as $balanceSheetId => $balanceSheetValues) {
			foreach ($balanceSheetValues as $balanceSheetItemId => $balanceSheetItemsValues) {
				$balanceSheetItem = BalanceSheetItem::find($balanceSheetItemId);
				$formattedData[$balanceSheetItem->name]['Name'] = $balanceSheetItem->name;
				foreach ($balanceSheetItemsValues as $date => $value) {
					$formattedData[$balanceSheetItem->name][$date] = in_array($balanceSheetItemId, $rateBalanceSheetItemsIds) ? number_format($value, 2) . ' %' : number_format($value);
				}
				$total = $totals[$balanceSheetId][$balanceSheetItemId];
				$formattedData[$balanceSheetItem->name]['Total'] = in_array($balanceSheetItemId, $rateBalanceSheetItemsIds) ? number_format($total, 2) . ' %' : number_format($total);
				if (isset($request->get('value')[$balanceSheetId][$balanceSheetItemId])) {
					foreach ($balanceSheetItemSubItems = $request->get('value')[$balanceSheetId][$balanceSheetItemId] as $balanceSheetItemSubItemName => $balanceSheetItemSubItemValues) {
						$formattedData[$balanceSheetItemSubItemName]['Name'] = $balanceSheetItemSubItemName;
						foreach ($balanceSheetItemSubItemValues as $balanceSheetItemSubItemDate => $balanceSheetItemSubItemValue) {
							$formattedData[$balanceSheetItemSubItemName][$balanceSheetItemSubItemDate] = in_array($balanceSheetItemId, $rateBalanceSheetItemsIds) ? number_format($balanceSheetItemSubItemValue, 2) . ' %' : number_format($balanceSheetItemSubItemValue);
						}
						$total = $subTotals[$balanceSheetId][$balanceSheetItemId][$balanceSheetItemSubItemName];
						$formattedData[$balanceSheetItemSubItemName]['Total'] = in_array($balanceSheetItemId, $rateBalanceSheetItemsIds) ? number_format($total, 2) . ' %' : number_format($total);
					}
				}
			}
		}
		return $formattedData;
	}
}
