<?php

namespace App\Http\Controllers;

use App\Helpers\HArr;
use App\Helpers\HDate;
use App\Http\Requests\CashFlowStatementRequest;
use App\Models\CashFlowStatement;
use App\Models\CashFlowStatementItem;
use App\Models\Company;
use App\Models\FinancialStatement;
use App\Models\IncomeStatement;
use App\Models\IncomeStatementItem;
use App\Models\IncomeStatementSubItem;
use App\Models\Repositories\CashFlowStatementRepository;
use App\ReadyFunctions\CollectionPolicyService;
use App\Services\VatCalculation;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CashFlowStatementController extends Controller
{
	private CashFlowStatementRepository $cashFlowStatementRepository;

	public function __construct(CashFlowStatementRepository $cashFlowStatementRepository)
	{
		// $this->middleware('permission:view branches')->only(['index']);
		// $this->middleware('permission:create branches')->only(['store']);
		// $this->middleware('permission:update branches')->only(['update']);
		$this->cashFlowStatementRepository = $cashFlowStatementRepository;
	}

	public function view()
	{
		return view('admin.cash-flow-statement.view', CashFlowStatement::getViewVars());
	}

	// public function create()
	// {
	// 	return view('admin.cash-flow-statement.create', CashFlowStatement::getViewVars());
	// }

	public function createReport(Company $company, CashFlowStatement $cashFlowStatement, $reportType = 'forecast')
	{
		// if first time
		$dates = array_keys($cashFlowStatement->getIntervalFormatted());
		if (
			Request()->route()->getName() == 'admin.show-cash-and-banks'
		) {
			$subItemType ='forecast';
			$receivables_and_payments = $cashFlowStatement
			->withSubItemsFor(CashFlowStatementItem::CASH_OUT_ID, $subItemType)
			->wherePivot('receivable_or_payment',  'payment')->get();
			
			$payments = $receivables_and_payments;
			$receivables = $cashFlowStatement
			->withSubItemsFor(CashFlowStatementItem::CASH_IN_ID, $subItemType)
			->wherePivot('receivable_or_payment', 'receivable')->get();
			
			$hasPayments = (bool)count($payments);
			$hasReceivables = (bool)count($receivables);
			$receivables_and_payments = $payments->concat($receivables);
			
			$model = $cashFlowStatement;
			if (!count($receivables_and_payments)) {
				$model = null;
			}
			$cashFlowStatement->update([
				'entered_receivables_and_payments_table'=>1
			]);
			$datesFormatted = $cashFlowStatement->getIntervalFormatted();
			
			return view('admin.cash-flow-statement.cash-opening-balance.create', ['dates'=>$dates,'datesFormatted'=>$datesFormatted, 'company'=>$company, 'cashFlowStatementId'=>$cashFlowStatement->id, 'receivables_and_payments'=>$receivables_and_payments, 'model'=>$model, 'subItemType'=>$subItemType,'hasPayments'=>$hasPayments,'hasReceivables'=>$hasReceivables]);
		}
		/**
		 * @var FinancialStatement $financialStatement
		 * @var CashFlowStatement $cashFlowStatement
		 */
		$financialStatement = $cashFlowStatement->financialStatement;
		$cashFlowStatement = $financialStatement->cashFlowStatement;
		$incomeStatement = $financialStatement->incomeStatement;
		$reportType = getReportNameFromRouteName(Request()->route()->getName()) ;
		$cashes = $this->calculateCashInAndCashOutFromIncomeStatementItems($incomeStatement , $reportType );
		return view('admin.cash-flow-statement.report.view', CashFlowStatement::getReportViewVars([
			'financial_statement_able_id' => $cashFlowStatement->id,
			'cashFlowStatement' => $cashFlowStatement,
			'reportType' => $reportType ,
			'cashes'=>$cashes
		]));
	}
	
	public function calculateCashInAndCashOutFromIncomeStatementItems(IncomeStatement $incomeStatement  , string $reportType)
	{
		$payloadsWithoutVat = [];
		$payloadsWithVat = [];
		$salesRevenuesForVat = [];
		$expensesForVat = [];
		$collectionPolicies = [];
		$cashes = [];
		$cashInKeyName = 'Cash In';
		$cashOutKeyName = 'Cash Out';
		/**
		 * @var FinancialStatement $financialStatement
		 */
		$financialStatement = $incomeStatement->financialStatement;
		$cashFlowStatement = $financialStatement->cashFlowStatement;
		$dates = $cashFlowStatement->getIntervalFormatted();
		$datesHelper = $financialStatement->getDatesIndexesHelper();
		$dateIndexWithDate = $datesHelper['dateIndexWithDate'];
	
		$receivables = $cashFlowStatement
		->withSubItemsFor(CashFlowStatementItem::CASH_IN_ID, $reportType)
		->wherePivot('receivable_or_payment', 'receivable')->get()->pluck('pivot');
		$payments = $cashFlowStatement
			->withSubItemsFor(CashFlowStatementItem::CASH_OUT_ID, $reportType)
			->wherePivot('receivable_or_payment',  'payment')->get()->pluck('pivot');
		$startDate = $financialStatement->getStartDate();
		$startDateFormatted = Carbon::make($startDate)->format('Y-m-d');
		$cashAndBanksBeginningBalance = $cashFlowStatement->cash_and_banks_beginning_balance ?: 0;
		$cashes[$cashInKeyName]['Cash & Banks Beginning Balance'] = [$startDateFormatted => $cashAndBanksBeginningBalance]; 
		$mainItems = IncomeStatementItem::where('has_sub_items',1)->where('is_sales_rate',0)->where('id','!=',IncomeStatementItem::CORPORATE_TAXES_ID)->get();
		foreach($mainItems as $mainItem){
			$mainItemCanBeDeductible = $mainItem->can_be_dedictiable;
			
			$mainItemId = $mainItem->id;
			$subItemsOfCurrentMainItem = $mainItem->load('subItems')->withSubItemsFor($incomeStatement->id , $reportType)->wherePivot('is_quantity',0)->wherePivot('is_depreciation_or_amortization',0)->get()->pluck('pivot');
			foreach($subItemsOfCurrentMainItem as $subItemOfCurrentMainItem){
				/**
				 * @var IncomeStatementSubItem $subItemOfCurrentMainItem
				 */
				$subItemName = $subItemOfCurrentMainItem->sub_item_name ;
				$subItemVatRate = $subItemOfCurrentMainItem->vat_rate ;
				$isDeductible = $subItemOfCurrentMainItem->is_deductible ;
				$payload = (array)json_decode($subItemOfCurrentMainItem->payload) ;
				$payload = $subItemOfCurrentMainItem->is_financial_expense ? removeMinusFromArr($payload) : $payload;
				$payloadAsDateStringValue = HDate::convertDateIndexArrayToDateString($payload,$dateIndexWithDate);
				$hasCollectionPolicy = $subItemOfCurrentMainItem->has_collection_policy;
				$collectionPolicyType = $subItemOfCurrentMainItem->collection_policy_type ;
				$collectionPolicyValue = $subItemOfCurrentMainItem->collection_policy_value;
				$collectionPolicyValue = $collectionPolicyType == 'customize' ? (array)json_decode($collectionPolicyValue) : $collectionPolicyValue;
				
				
				if($subItemOfCurrentMainItem->financial_statement_able_item_id == IncomeStatementItem::SALES_REVENUE_ID){
					$collectionPolicies[$mainItemId][$subItemName] = (new CollectionPolicyService())->applyCollectionPolicy($hasCollectionPolicy, $collectionPolicyType, $collectionPolicyValue, $this->calculateVat($payloadAsDateStringValue,$subItemVatRate));
					$salesRevenuesForVat[] = [
						'vat_rate'=>$subItemVatRate,
						'values'=>changeDateFormatOfArrTo($payloadAsDateStringValue,'d-m-Y')
					];
				}
				elseif($mainItemCanBeDeductible && $isDeductible){
					$collectionPolicies[$mainItemId][$subItemName] = (new CollectionPolicyService())->applyCollectionPolicy($hasCollectionPolicy, $collectionPolicyType, $collectionPolicyValue, $this->calculateVat($payloadAsDateStringValue,$subItemVatRate));
						$expensesForVat[] = [
							'vat_rate'=>$subItemVatRate,
							'values'=>changeDateFormatOfArrTo($payloadAsDateStringValue,'d-m-Y')
						];
				}else{
					$collectionPolicies[$mainItemId][$subItemName] = (new CollectionPolicyService())->applyCollectionPolicy($hasCollectionPolicy, $collectionPolicyType, $collectionPolicyValue, $payloadAsDateStringValue);
				
				}
				if($subItemOfCurrentMainItem->financial_statement_able_item_id == IncomeStatementItem::SALES_REVENUE_ID || $subItemOfCurrentMainItem->is_financial_income){
					$cashes[$cashInKeyName][$subItemName]=$collectionPolicies[$mainItemId][$subItemName];
				
				}else{
					$cashes[$cashOutKeyName][$subItemName]=$collectionPolicies[$mainItemId][$subItemName];
				}
			}
		}

			foreach($receivables as $receivable){
				$cashes[$cashInKeyName][$receivable->sub_item_name]=(array) json_decode($receivable->payload);
			}
			foreach($payments as $payment){
				$cashes[$cashOutKeyName][$payment->sub_item_name]=(array) json_decode($payment->payload);
			}
			$vatCalculationService = new VatCalculation();
			$vatCalculations = $vatCalculationService->__execute($salesRevenuesForVat , [] , $expensesForVat , [] , $cashFlowStatement->FinancialStatement->getStartDateFormatted()  ,$cashFlowStatement->FinancialStatement->getDuration());
			$vatPayment = $vatCalculations['monthly']['VAT Payment'] ?? [];
			$vatPayment = changeDateFormatOfArrTo($vatPayment,'Y-m-d');
			$vatPaymentSubItem = 'VAT Payment' ;
			$cashes[$cashOutKeyName][$vatPaymentSubItem ]=$vatPayment;
			$this->calculateTotalAndAccumulated($cashes,$cashInKeyName , $cashOutKeyName,$dates);
		
		return $cashes ;
	}
	protected function calculateVat(array $items , float $vatRate ){
		$result = [];
		foreach($items as $date=>$value){
			$result[$date] = $value * (1+($vatRate/100));
		}
		return $result; 
	}
	protected function calculateTotalAndAccumulated(&$cashes,$cashInKeyName,$cashOutKeyName,$dates)
	{
		foreach($cashes as $cashName => $subElements){
			$cashes[$cashName]['total'] = getTotalOf($subElements);
		}
		$cashes['Monthly Net Cash'] = ['total'=>HArr::subtractAtDates([$cashes[$cashInKeyName]['total'] ?? [], $cashes[$cashOutKeyName]['total']??[]  ],$dates)];
		$cashes['Accumulated Net Cash'] = ['total'=>HArr::accumulateArray($cashes['Monthly Net Cash']['total']??[])];
	}
	protected function calculateVatRate(array $payload , float $vatRate ):array 
	{
		$items = [];
		if($vatRate == 0){
			return $payload ; 
		}
		foreach($payload as $date => $value){
			$items[$date] = $value * (1+$vatRate/100) ;	
		}
		return $items ; 
	}

	public function paginateReport(Request $request, Company $company, CashFlowStatement $cashFlowStatement)
	{
		return $this->cashFlowStatementRepository->paginateReport($request, $cashFlowStatement);
	}

	public function store(CashFlowStatementRequest $request)
	{
		$cashFlowStatement = $this->cashFlowStatementRepository->store($request);

		return response()->json([
			'status' => true,
			'message' => __('Income Statement Has Been Stored Successfully'),
			'redirectTo' => route('admin.create.income.statement.report', ['company' => getCurrentCompanyId(), 'cashFlowStatement' => $cashFlowStatement->id])
		]);
	}

	public function storeReport(Request $request)
	{
		$this->cashFlowStatementRepository->storeReport($request);

		return response()->json([
			'status' => true,
			'message' => __('Income Statement Has Been Stored Successfully')
		]);
	}


	public function storeCashAndBanks($company, Request $request)
	{
		$company = Company::find($company);
		$cashFlowStatementId = $request->get('cash_flow_statement_id');
		$subItemType  = $request->get('subItemType');
		$cashFlowStatement = CashFlowStatement::find($cashFlowStatementId);

		$cashFlowStatement->update([
			'cash_and_banks_beginning_balance'=>$request->get('cash_and_banks_beginning_balance')
		]);
		$dates = (array)$request->get('dates');


		$cashItemId = CashFlowStatementItem::CASH_IN_ID;
		$cashAndBanksName = 'Cash & Banks Beginning Balance';
		$lastIndex = count((array)$request->get('opening_receivable')) - 1;

		$cashFlowStatement->withSubItemsFor(CashFlowStatementItem::CASH_IN_ID, $subItemType)->wherePivot('receivable_or_payment', '!=', null)->detach();
		$cashFlowStatement->withSubItemsFor(CashFlowStatementItem::CASH_OUT_ID, $subItemType)->wherePivot('receivable_or_payment', '!=', null)->detach();


		foreach ((array)$request->get('opening_receivable') as $index => $arr) {
			$payload = [];
			foreach ($dates as $date) {
				$payload[$date]=$arr[$date] ?? 0;
			}
			$data = [
				'sub_item_name'=> $name = $arr['receivable_name'],
				'sub_item_type'=> $subItemType,
				'created_from'=>$subItemType,
				'payload'=>json_encode($payload),
				'is_depreciation_or_amortization'=>0,
				'has_collection_policy'=>0,
				'is_quantity'=>0,
				'can_be_quantity'=>0,
				'can_be_percentage_or_fixed'=>0,
				'company_id'=>$company->id,
				'percentage_or_fixed'=>'non_repeating_fixed',
				'creator_id'=>auth()->user()->id,
				'ordered'=>3 ,
				// 'balance_amount'=>$arr['balance_amount'],
				// 'payload'=>$payload,
				// 'cash_flow_statement_id'=>$cashFlowStatementId,
				'receivable_or_payment'=>'receivable',
				'created_at'=>now()
			];
			// $data = [
			// 	'name'=> $name = $arr['receivable_name'] ,
			// 	'balance_amount'=>$arr['balance_amount'],
			// 	'payload'=>$payload,
			// 	'cash_flow_statement_id'=>$cashFlowStatementId,
			// 	'type'=>'payment',
			// 	'created_at'=>now()
			// ];
			// if ($arr['id']) {

				if($name){
					$cashFlowStatement->withSubItemsFor($cashItemId, $subItemType, $name)->attach($cashItemId, $data);
				}

				if ($index == $lastIndex) {
					$cashFlowStatement->withSubItemsFor($cashItemId, $subItemType, $cashAndBanksName)->attach(
						$cashItemId,
						array_merge($data, [
							'sub_item_name'=>$cashAndBanksName,
							'payload'=>json_encode([$dates[0]=>$request->get('cash_and_banks_beginning_balance')]),
							'receivable_or_payment'=>'cash_and_banks_beginning_balance',
							'ordered'=>1 
						])
					);
				}

				
			
		}

		$cashItemId  = CashFlowStatementItem::CASH_OUT_ID;

		foreach ((array)$request->get('opening_payment') as $index => $arr) {
			$payload = [];
			foreach ($dates as $date) {
				$payload[$date]=$arr[$date] ?? 0;
			}

			$data = [
				'sub_item_name'=> $name = $arr['receivable_name'],
				'sub_item_type'=> $subItemType,
				'created_from'=>$subItemType,
				'payload'=>json_encode($payload),
				'is_depreciation_or_amortization'=>0,
				'has_collection_policy'=>0,
				'is_quantity'=>0,
				'can_be_quantity'=>0,
				'ordered'=>4 ,
				'can_be_percentage_or_fixed'=>0,
				'company_id'=>$company->id,
				'percentage_or_fixed'=>'non_repeating_fixed',
				'creator_id'=>auth()->user()->id,
				// 'balance_amount'=>$arr['balance_amount'],
				// 'payload'=>$payload,
				// 'cash_flow_statement_id'=>$cashFlowStatementId,
				'receivable_or_payment'=>'payment',
				'created_at'=>now()
			];
			if($name){
				$cashFlowStatement->withSubItemsFor($cashItemId, $subItemType, $name)->attach($cashItemId, $data);
			}
			
			
		}

		return redirect()->route('admin.create.cash.flow.statement.forecast.report', ['cashFlowStatement'=>$cashFlowStatementId, 'company'=>$company->id]);
	}
}
