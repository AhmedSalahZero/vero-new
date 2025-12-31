<?php

namespace App\Http\Controllers;

use App\Exports\IncomeStatementExport;
use App\Exports\IncomeStatementExportAsPdf;
use App\Http\Requests\IncomeStatementRequest;
use App\Http\Requests\StoreIncomeStatementReportRequest;
use App\Jobs\RecalculateIncomeStatementCalculationForTypesJob;
use App\Models\Company;
use App\Models\IncomeStatement;
use App\Models\IncomeStatementItem;
use App\Models\Repositories\CashFlowStatementRepository;
use App\Models\Repositories\IncomeStatementRepository;
use App\Rules\MustBeUniqueToIncomeStatementExceptMine;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Validator;

class IncomeStatementController extends Controller
{

	private IncomeStatementRepository $incomeStatementRepository;

	public function __construct(IncomeStatementRepository $incomeStatementRepository)
	{
		// $this->middleware('permission:view branches')->only(['index']);
		// $this->middleware('permission:create branches')->only(['store']);
		// $this->middleware('permission:update branches')->only(['update']);
		$this->incomeStatementRepository = $incomeStatementRepository;
	}

	public function view()
	{
		return view('admin.income-statement.view', IncomeStatement::getViewVars());
	}
	public function create()
	{
		
		return view('admin.income-statement.create', IncomeStatement::getViewVars());
	}

	public function createReport(Company $company, IncomeStatement $incomeStatement)
	{
		$reportType  = getReportNameFromRouteName(Request()->route()->getName()) ;
		if($incomeStatement->{'is_caching_'.$reportType}){
			return redirect()->route('admin.view.financial.statement',['company'=>$company->id])->with('fail',__('Please Wait A Second'));
		}
		
		// $cashFlowStatement = $incomeStatement->financialStatement->cashFlowStatement;
		$additionalVarOptions = [
			'financial_statement_able_id' => $incomeStatement->id,
			'incomeStatement' => $incomeStatement,
			'reportType' => $reportType ,
			 
		] ;
		
		return view('admin.income-statement.report.view', IncomeStatement::getReportViewVars($additionalVarOptions));
	}

	public function paginate(Request $request)
	{
		return $this->incomeStatementRepository->paginate($request);
	}
	public function paginateReport(Request $request, Company $company, IncomeStatement $incomeStatement)
	{
		return $this->incomeStatementRepository->paginateReport($request, $incomeStatement);
	}


	public function store(IncomeStatementRequest $request)
	{
		$incomeStatement = $this->incomeStatementRepository->store($request);
		
		return response()->json([
			'status' => true,
			'message' => __('Income Statement Has Been Stored Successfully'),
			'redirectTo' => route('admin.create.income.statement.report', ['company' => getCurrentCompanyId(), 'incomeStatement' => $incomeStatement->id])
		]);
	}

	public function storeReport(StoreIncomeStatementReportRequest $request)
	{
		$request->merge([
			'value'
		]);
		$this->incomeStatementRepository->storeReport($request);
		// return redirect()->back()->with('success',__('Income Statement Has Been Stored Successfully'));
		
		return redirect()->back();
		// return response()->json([
		// 	'status' => true,
		// 	'message' => __('Income Statement Has Been Stored Successfully')
		// ]);
	}

	public function edit(Company $company, Request $request, IncomeStatement $incomeStatement)
	{
		return view(IncomeStatement::getCrudViewName(), array_merge(IncomeStatement::getViewVars(), [
			'type' => 'edit',
			'model' => $incomeStatement
		]));
	}

	public function update(Company $company, Request $request, IncomeStatement $incomeStatement)
	{
		
		$this->incomeStatementRepository->update($incomeStatement, $request);
		return response()->json([
			'status' => true,
			'message' => __('Income Statement Has Been Updated Successfully')
		]);
	}

	public function updateReport(Company $company, Request $request)
	{
		

		$incomeStatementId = $request->get('financial_statement_able_id');
		$incomeStatementItemId = $request->get('financial_statement_able_item_id');
		$incomeStatement = IncomeStatement::find($incomeStatementId);
		$incomeStatementItem = $incomeStatement->withMainItemsFor($incomeStatementItemId)->first();
		$currentSubItemType = $request->get('sub_item_type'); 
		$subItem = $incomeStatementItem
		->withSubItemsFor($incomeStatementId,$currentSubItemType, $request->get('sub_item_name'))
		->first();
		$subItemPivot = $subItem->pivot;
		$id = $subItemPivot->id ;
				
		$validator = Validator::make($request->all(),[
			'new_sub_item_name'=>['sometimes',new MustBeUniqueToIncomeStatementExceptMine($company->id , $incomeStatementId,$currentSubItemType,$id)]
		]);
		
		if($validator->fails()){
			return response()->json([
				'message' => $validator->errors()->first(),
				'status' => false
			]);
		}
		
		
		$itemsToUpdateName =[];
		$quantityItemsToUpdateName = [];
		
		$subItemTypesToDetach = getIndexesLargerThanOrEqualIndex(getAllFinancialAbleTypes(), $request->get('sub_item_type'));
		// $collection_value = '';
		// $collectionPolicyType = $request->input('sub_items.0.collection_policy.type.name') ;
		// $collection_value_arr = $request->input('sub_items.0.collection_policy.type.'.$collectionPolicyType.'.value');
		// if (isset($collection_value_arr) && is_array($collection_value_arr)) {
			// $collection_value = json_encode($collection_value_arr);
		// } elseif (isset($collection_value_arr)) {
			// $collection_value = $collection_value_arr;
		// }
		foreach ($subItemTypesToDetach as $index=>$subItemType) {
			#NOTE:We update Single Item From Popup Here
			
				$currentSubItem = $incomeStatementItem
				->withSubItemsFor($incomeStatementId, $subItemType, $request->get('sub_item_name')) ;
				$itemsToUpdateName[$subItemType] =  ['item'=>$currentSubItem,'new_name'=>html_entity_decode($request->get('new_sub_item_name'))];
				$subItems = $request->get('sub_items');
				$subItems[0]['name']=$request->get('sub_item_name');
				$request->merge([
					'sub_items'=>$subItems
				]);
		
				
		//		if($incomeStatementId == IncomeStatementItem::SALES_REVENUE_ID){
					$currentQuantitySubItem = $incomeStatementItem->withSubItemsFor($incomeStatementId, $subItemType, $request->get('sub_item_name'). quantityIdentifier);
					$quantityItemsToUpdateName[$subItemType] = ['item'=>$currentQuantitySubItem,'new_name'=>html_entity_decode($request->get('new_sub_item_name') . quantityIdentifier)];
					
			//	}
				// $start = microtime(true);
				if($index == 0){
					$incomeStatement->storeReport($request);
				}
				//$incomeStatement->syncPivotFor($incomeStatementItemId, $subItemType, $request->get('sub_item_name') . __(quantityIdentifier),$currentDataForQuantity,null);
				
		}

		foreach($itemsToUpdateName as $subItemType => $subItemArr){
			$subItem = $subItemArr['item'];
			$newName = $subItemArr['new_name'];
			$subItem->updateExistingPivot($incomeStatementId,[
				'sub_item_name'=>$newName
			]);
		}
	
		foreach($quantityItemsToUpdateName as $subItemType => $subItemArr){
			$subItem = $subItemArr['item'];
			$newName = $subItemArr['new_name'];
			$subItem->updateExistingPivot($incomeStatementId,[
				'sub_item_name'=>$newName
			]);
		}
		/**
		 * @var incomeStatement $incomeStatement
		 */
		// $incomeStatement->storeReport($request);
		
	
		
		return response()->json([
			'status' => true,
			'message' => __('Item Has Been Updated Successfully')
		]);
	}

	public function deleteReport(Company $company, Request $request)
	{
		$incomeStatementId = $request->get('financial_statement_able_id');
		$incomeStatementItemId = $request->get('financial_statement_able_item_id');
		$incomeStatement = IncomeStatement::find($incomeStatementId);
		$subItemsNames = formatSubItemsNamesForQuantity($request->get('sub_item_name'));

	//	$isFinancialIncome = (bool)$request->get('is_financial_income');
		// $incomeStatement->storeReport($request);
		$incomeStatementItem = $incomeStatement->withMainItemsFor($incomeStatementItemId)->first();

		$subItemTypesToDetach = getIndexesLargerThanOrEqualIndex(getAllFinancialAbleTypes(), $request->get('sub_item_type'));
		
		foreach ($subItemTypesToDetach as $index=>$subItemType) {
			foreach ($subItemsNames as $subItemName) {
				$incomeStatementItem->withSubItemsFor($incomeStatementId, $subItemType, $subItemName)->detach($incomeStatementId);
			}
			if($index ==0 ) // current type
				{
					$incomeStatement->refreshCalculationFor($subItemType);
				}else{
					$incomeStatement['is_caching_'.$subItemType] = 1 ;
					$incomeStatement->save();
						$job = (new RecalculateIncomeStatementCalculationForTypesJob($incomeStatement,$subItemType));
						dispatch($job)	;
						
						// $incomeStatement->refreshCalculationFor($insertSubItem);
					
				}
				
			// if ($subItemType != $request->get('sub_item_type')) {
				// $incomeStatement->refreshCalculationFor($subItemType);
			// }
		}
	

		return response()->json([
			'status' => true,
			'message' => __('Item Has Been Deleted Successfully')
		]);
	}
	public function export(Request $request)
	{
		// return (new IncomeStatementExport($this->incomeStatementRepository->export($request), $request))->download();
	}
	public function exportReport(Request $request,Company $company , int $incomeStatementId , string $subItemType)
	{
		$formattedData = $this->formatReportDataForExport($request,$incomeStatementId,$subItemType)['data'];
		
		// $incomeStatementId = array_key_first($request->get('valueMainRowThatHasSubItems'));
		$incomeStatement = IncomeStatement::find($incomeStatementId);
		$reportType = $request->input('sub_item_type');
		return (new IncomeStatementExport(collect($formattedData), $request, $incomeStatement,$reportType))->download();
	}
	public function exportReportAsPdf(Request $request ,Company $company, int $incomeStatementId,string $subItemType)
	{
		$reportType = $request->input('sub_item_type');
		$incomeStatement = IncomeStatement::find($incomeStatementId);
		$reportItems = $this->formatReportDataForExport($request,$incomeStatementId,$subItemType) ;
		$formattedData = $reportItems['data'];
		$mainRowsIndexes = array_keys($reportItems['mainRowsIndexes']);
		$percentageRowsIndexes = array_keys($reportItems['percentageRowsIndexes']);
		$subRowsIndexes = array_keys($reportItems['subRowsIndexes']);
		$maxColsCount = $reportItems['maxColsCount'];
		$maxRowsCount = $reportItems['maxRowsCount'];
		
		// $incomeStatementId = array_key_first($request->get('valueMainRowThatHasSubItems'));
		
		// return (new IncomeStatementExport(collect($formattedData), $request, $incomeStatement))->download();
		return (new IncomeStatementExportAsPdf(collect($formattedData), $request, $incomeStatement,$mainRowsIndexes,$percentageRowsIndexes,$subRowsIndexes,$maxColsCount,$maxRowsCount,$reportType))->download($incomeStatement->getName().'.pdf','Dompdf');
		
	}
	protected function combineMainValuesWithItsPercentageRows(array $firstItems, array $secondItems): array
	{
		$mergeArray = [];
		foreach ($firstItems as $incomeStatementId => $incomeStatementValues) {
			foreach ($incomeStatementValues as $incomeStatementItemId => $incomeStatementItemsValues) {
				foreach ($incomeStatementItemsValues as $date => $value) {
					$mergeArray[$incomeStatementId][$incomeStatementItemId][$date] = $value;
				}
			}
		}
		foreach ($secondItems as $incomeStatementId => $incomeStatementValues) {
			foreach ($incomeStatementValues as $incomeStatementItemId => $incomeStatementItemsValues) {
				foreach ($incomeStatementItemsValues as $date => $value) {
					$mergeArray[$incomeStatementId][$incomeStatementItemId][$date] = $value;
				}
			}
		}

		$mergeArray[$incomeStatementId] = orderArrayByItemsKeys($mergeArray[$incomeStatementId]);

		return $mergeArray;
	}
	public function formatReportDataForExport(Request $request,int $incomeStatementId, string $subItemType)
	{
		$dynamicRowsShow = (bool) $request->get('dynamic_rows_shown');
		$opensMainRows = (array)json_decode($request->opens) ;
		$numberOfColumnBeforeDates = 1 ; // name column
		$numberOfColumnAfterDates = 1 ; // total column
		$staticHeaderRows = 2 ; 
		$formattedData = [];
		$totals = $request->get('totals');
		$subTotals = $request->get('subTotals');
		$incomeStatement = IncomeStatement::find($incomeStatementId);
		$rateIncomeStatementItemsIds = IncomeStatementItem::rateFieldsIds();
		$maxRowsCount = 0 ;
		$index =$staticHeaderRows+1   ; 
		$mainRowsIndexes = [] ;
		$percentageRowsIndexes = [] ;
		$subRowsIndexes = [] ;
		$allMainItems = $incomeStatement->mainItems()->get();
		// $subItemType ='forecast';
		$combineMainValuesWithItsPercentageRows = [];
		$currentPayload = [];
		foreach($allMainItems as $mainItem){
			$incomeStatementItemId = $mainItem->id ;
			$mainRowWithAuthCalculation = $incomeStatement->withMainRowsFor($incomeStatementItemId, $subItemType)->first();
			$mainRowWithAuthCalculation->pivot->payload;
			$currentPayload = (array) json_decode($mainRowWithAuthCalculation->pivot->payload) ;
			$combineMainValuesWithItsPercentageRows[$incomeStatementId][$incomeStatementItemId] =$currentPayload ;
		}
		$datesFormatted = $incomeStatement->getIntervalFormatted();
		$datesCount = count($currentPayload);
		$maxColsCount = $numberOfColumnBeforeDates +  $datesCount + $numberOfColumnAfterDates ; 
		foreach ($combineMainValuesWithItsPercentageRows as $incomeStatementId => $incomeStatementValues) {
			
			foreach ($incomeStatementValues as $incomeStatementItemId => $incomeStatementItemsValues) {
				$incomeStatementItem = IncomeStatementItem::find($incomeStatementItemId);
				$formattedData[$incomeStatementItem->name]['Name'] = $incomeStatementItem->name;
				if(isPercentageOrRate($incomeStatementItem->name)){
					$percentageRowsIndexes[$index]=$incomeStatementItem->name;
				}else{
					$mainRowsIndexes[$index]=$incomeStatementItem->name;
				}
				$index++;
				if($index > $maxRowsCount){
					$maxRowsCount = $index;
				}
			
				foreach ($incomeStatementItemsValues as $dateAsIndex => $value) {
					$dateAsString = formatDateForView($datesFormatted[$dateAsIndex]);
					$formattedData[$incomeStatementItem->name][$dateAsString] = in_array($incomeStatementItemId, $rateIncomeStatementItemsIds) ? number_format($value, 2) . ' %' : number_format($value);
				}
				$total = $totals[$incomeStatementId][$incomeStatementItemId];
				
				$formattedData[$incomeStatementItem->name]['Total'] = in_array($incomeStatementItemId, $rateIncomeStatementItemsIds) ? number_format($total, 2) . ' %' : number_format($total);
				if (isset($request->get('value')[$incomeStatementId][$incomeStatementItemId])) {
					foreach ($incomeStatementItemSubItems = $request->get('value')[$incomeStatementId][$incomeStatementItemId] as $incomeStatementItemSubItemName => $incomeStatementItemSubItemValues) {
						if(in_array($incomeStatementItemId,$opensMainRows) || !$dynamicRowsShow){
							$formattedData[$incomeStatementItemSubItemName.'-'.$incomeStatementItem->name]['Name'] = $incomeStatementItemSubItemName;
							
							$subRowsIndexes[$index]=$incomeStatementItemSubItemName;
							$index++;
							if($index > $maxRowsCount){
								$maxRowsCount = $index;
							}
							foreach ($incomeStatementItemSubItemValues as $incomeStatementItemSubItemDate => $incomeStatementItemSubItemValue) {
								$formattedData[$incomeStatementItemSubItemName.'-'.$incomeStatementItem->name][$incomeStatementItemSubItemDate] = in_array($incomeStatementItemId, $rateIncomeStatementItemsIds) ? number_format($incomeStatementItemSubItemValue, 2) . ' %' : number_format($incomeStatementItemSubItemValue);
							}
							$total = $subTotals[$incomeStatementId][$incomeStatementItemId][$incomeStatementItemSubItemName];
							$formattedData[$incomeStatementItemSubItemName.'-'.$incomeStatementItem->name]['Total'] = in_array($incomeStatementItemId, $rateIncomeStatementItemsIds) ? number_format($total, 2) . ' %' : number_format($total);
							
							
							
						}
							}
				}
			}
		}
		return [
			'data'=>$formattedData,
			'mainRowsIndexes'=>$mainRowsIndexes,
			'percentageRowsIndexes' =>$percentageRowsIndexes ,
			'subRowsIndexes' => $subRowsIndexes ,
			'maxColsCount'=>$maxColsCount,
			'maxRowsCount'=>$maxRowsCount
		];
	}
}
