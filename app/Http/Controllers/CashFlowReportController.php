<?php

namespace App\Http\Controllers;

use App\Helpers\HArr;
use App\Models\CashExpense;
use App\Models\CashflowReport;
use App\Models\Cheque;
use App\Models\Company;
use App\Models\Contract;
use App\Models\CustomerInvoice;
use App\Models\ForeignExchangeRate;
use App\Models\LetterOfCreditIssuance;
use App\Models\LetterOfGuaranteeIssuance;
use App\Models\LoanSchedule;
use App\Models\MoneyPayment;
use App\Models\MoneyReceived;
use App\Models\PayableCheque;
use App\Models\PoAllocation;
use App\Models\SettlementAllocation;
use App\Models\SupplierInvoice;
use App\Models\TimeOfDeposit;
use App\Traits\GeneralFunctions;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CashFlowReportController
{
    use GeneralFunctions;
    public function index(Company $company)
	{
		$cashflowReports = $company->cashflowReports->where('is_contract',0);
        return view('reports.cash_flow_form',[
			'company'=>$company,
			'cashflowReports'=>$cashflowReports
		]);
    }
	public function getRedirectRoute(bool $isContract):string 
	{
		return $isContract ?'result.contract.cashflow.report' :'result.cashflow.report';
	}
	
	public function result(Company $company , Request $request, bool $returnResultAsArray = false ,  CashFlowReport $cashflowReport= null   , $defaultCashFlowId = 0 ){
		$saveReport = $request->has('save_report');
		$resetReport = $request->has('reset_report') && $request->get('reset_report');
		$contractId = $request->get('contract_id')	 ;
		$contract = Contract::find($contractId);
		/**
		 * @var Contract $contract 
		 */
		$contractCode = $contract ? $contract->getCode() : null ;
		$contractName = $contract ? $contract->getName() : null ;
		$customer = $contract ? $contract->client : null ;
		$customerId = $customer ? $customer->getId() : null ;
		$customerName = $customer ? $customer->getName() : null ;
		$isContract = (bool)$customerId;
		$redirectRouteName = $this->getRedirectRoute($isContract);
		
		$cashflowReportId = $cashflowReport && $cashflowReport->id ? $cashflowReport->id : $defaultCashFlowId;
		if( $resetReport && !session()->has('without_resetting') ){
			$company->resetCashFlowReport();			
			$queryParams = $request->query();
			$queryParams['reset_report'] = 0;
			$queryParams['company'] = $company->id;
			if($cashflowReportId){
				$queryParams['cashflowReport'] = $cashflowReportId;
				if($contractId){
					$queryParams['contract_id'] = $contractId;
				}
			}
			return redirect()->route($redirectRouteName,  $queryParams);
		}
		if($cashflowReport && $cashflowReport->report_data){
			$reportData = json_decode($cashflowReport->report_data,true);
			$currencyName = Arr::first($reportData['allCurrencies']);
			return view('admin.reports.contract-cash-flow-report',array_merge($reportData,['cashflowReport'=>$cashflowReport,'currencyName'=>$currencyName,'contractCode'=>$contractCode]));
		}
			$mainFunctionalCurrency= $company->getMainFunctionalCurrency();
		$isContract = (bool)$contract ;
		$currencyName = $isContract ? $contract->getCurrency(): $request->get('currency',$mainFunctionalCurrency);
		
		
		$customerContractId = $contractId ;
		
		$poAllocations = PoAllocation::where('po_allocations.contract_id',$customerContractId)	
		->join('purchase_orders','purchase_orders.id','=','po_allocations.purchase_order_id')
		->join('contracts','contracts.id','=','purchase_orders.contract_id')
		->get(); ;
		$defaultStartDate = $request->get('cash_start_date',now()->format('Y-m-d'));
		$defaultEndDate = $request->get('cash_end_date',now()->addMonth()->format('Y-m-d'));
		$formStartDate =Carbon::make($request->get('start_date',$defaultStartDate))->format('Y-m-d'); 
		$formEndDate =Carbon::make($request->get('end_date',$defaultEndDate))->format('Y-m-d');
		if(!now()->between($formStartDate,$formEndDate)){
			return redirect()->back()->with('fail',__('Kindly the date of Today must be included within the report duration'));
		}
		$reportInterval =  $request->get('report_interval','weekly');
		$title = $request->has('title') ? $request->get('title') : __('Company Cash Flow') . ' [ ' . $reportInterval . ' ]' ;
		
		// $reportInterval = 'daily';
		$result = [];
		$letterOfGuaranteeModelData = [];
		// $cashExpenseCategoryNamesArr = [];
		$pastDueSupplierInvoicesForContracts = collect([]);
		$result['customers']=[
			'Cash & Banks Balance'=>[],
			'Checks Collected'=>[],
			'Incoming Transfers'=>[],
			'Bank Deposits'=> [],
			'Cash Collections'=> [],
			'Time Of Deposits'=> [],
			'Cheques Under Collection'=>[],
			'Cheques In Safe'=>[],
			'Cancelled LGs Cash Cover'=>[],
			'Customers Invoices'=>[],
			'Customers Past Due Invoices'=>[],
			'Forecasted Project Collection'=>[],
			'Projected Other Cash In Items'=>[],
			__('Total Cash Inflow')=>[]
		];
		if($contractId){
			unset($result['customers']['Cash & Banks Balance']);
			unset($result['customers']['Time Of Deposits']);
		}
		
		$noRowHeaders =  $reportInterval == 'weekly' ? 3 : 1 ;
		
		$months = generateDatesBetweenTwoDates(Carbon::make($formStartDate),Carbon::make($formEndDate)); 
		$days = generateDatesBetweenTwoDates(Carbon::make($formStartDate),Carbon::make($formEndDate),'addDay'); 
		$startDate = Carbon::make($request->get('start_date',$defaultStartDate))->format('Y-m-d');
		$currency = $request->get('currency');
		
		if(is_null($currency) && $contract){
			$currency = $contract->getCurrency();
		}else{
			$currency = $company->getMainFunctionalCurrency();
		}
		$year = explode('-',$startDate)[0];
		$endDate  = Carbon::make($request->get('end_date',$defaultEndDate))->format('Y-m-d');
		$redirectRouteName = $this->getRedirectRoute($isContract);
	
		$datesWithWeeks = [];
		if($reportInterval == 'weekly'){
			$datesWithWeeks = 	getWeekNumberBetweenDates($year , Carbon::make($endDate)) ;
		}
		elseif($reportInterval == 'monthly'){
			$datesWithWeeks = 	getMonthNumberBetweenDates($year , Carbon::make($endDate)) ;
		}
		elseif($reportInterval == 'daily'){
			$datesWithWeeks = 	getDayNumberBetweenDates($year , Carbon::make($endDate)) ;
		}
		$weeks  = $this->mergeYearWithWeek($datesWithWeeks ,Carbon::make($startDate) );
		$datesWithWeekNumber  = $this->getDateWithWeakNumber($datesWithWeeks ,Carbon::make($startDate) );
		$foreignExchangeRates = ForeignExchangeRate::where('company_id',$company->id)->get();
		$firstIndex = array_key_first($weeks);
		$lastIndex = array_key_last($weeks);
		$dates = [];
		$rangedWeeks = [];
		CashExpense::getProjectionOtherCashOut($result ,$company,$cashflowReportId,$isContract) ;
		  if(!$contractId){
		      CustomerInvoice::getCashAndBankBalanceAtDate($result ,$foreignExchangeRates,$mainFunctionalCurrency,$startDate ,array_keys($weeks)[0],$company->id) ;
			  LoanSchedule::getLoanInstallmentsAtDates($result,$foreignExchangeRates,$mainFunctionalCurrency,$company->id,$datesWithWeekNumber,$endDate);
		}
		
		  CustomerInvoice::getProjectionOtherCashIn($result ,$company,$cashflowReportId,$isContract) ;
		  /**
		   * ! start postponed
		   */
		  CustomerInvoice::getForecastedProjectCollection($result ,$startDate , $endDate,$currency,$company->id,$datesWithWeekNumber,$contractId) ;
		   SupplierInvoice::getForecastedProjectCollection($result ,$startDate , $endDate,$currency,$company->id,$datesWithWeekNumber,$contractId) ;
		
		 /**
		   * ! end postponed
		   */
		  
		  CustomerInvoice::getCustomerInvoicesUnderCollectionAtDatesForContracts($result,$company->id,$contractCode,$datesWithWeekNumber,$endDate);
		
		  $isContract ? SupplierInvoice::getSupplierInvoicesForPoUnderCollectionAtDates($result,$company->id,$datesWithWeekNumber,$startDate,$endDate,$poAllocations,$pastDueSupplierInvoicesForContracts) : SupplierInvoice::getSupplierInvoicesUnderCollectionAtDates($result,$company->id,$datesWithWeekNumber,$startDate,$endDate);
	
		foreach($weeks as $currentWeekYear=>$week){
			
			$currentYear = explode('-',$currentWeekYear)[1];
			if($currentWeekYear == $firstIndex){
				$startDate = $startDate ;
				$endDate = getMinDateOfWeek($datesWithWeeks,$week,$currentYear)['end_date'];
			}
			elseif($currentWeekYear == $lastIndex){
				$startDate = getMinDateOfWeek($datesWithWeeks,$week,$currentYear)['start_date'];
				$endDate = $request->get('end_date',$defaultEndDate);  
			}
			else
			{
				$rangedWeeks = getMinDateOfWeek($datesWithWeeks,$week,$currentYear);
				$startDate = $rangedWeeks['start_date'];
				$endDate = $rangedWeeks['end_date'];
			}
			CustomerInvoice::getSettlementAmountUnderDateForSpecificType($result,$foreignExchangeRates,$mainFunctionalCurrency ,MoneyReceived::CHEQUE,'expected_collection_date',$startDate , $endDate,$contractCode,$currentWeekYear,Cheque::UNDER_COLLECTION,$company->id) ;

			CustomerInvoice::getSettlementAmountUnderDateForSpecificType($result,$foreignExchangeRates,$mainFunctionalCurrency,MoneyReceived::CHEQUE,'actual_collection_date',$startDate , $endDate,$contractCode,$currentWeekYear,Cheque::COLLECTED,$company->id);

			CustomerInvoice::getSettlementAmountUnderDateForSpecificType($result,$foreignExchangeRates,$mainFunctionalCurrency ,MoneyReceived::INCOMING_TRANSFER,'receiving_date',$startDate , $endDate,$contractCode,$currentWeekYear,null,$company->id);

			CustomerInvoice::getSettlementAmountUnderDateForSpecificType($result,$foreignExchangeRates,$mainFunctionalCurrency , MoneyReceived::CASH_IN_BANK,'receiving_date',$startDate , $endDate,$contractCode,$currentWeekYear,null,$company->id);

			CustomerInvoice::getSettlementAmountUnderDateForSpecificType($result,$foreignExchangeRates,$mainFunctionalCurrency , MoneyReceived::CASH_IN_SAFE,'receiving_date',$startDate , $endDate,$contractCode,$currentWeekYear,null,$company->id);
			
			if($contractId){
				CustomerInvoice::getDownPaymentsOverContracts($result,$foreignExchangeRates,$mainFunctionalCurrency ,MoneyReceived::CHEQUE,'expected_collection_date',$startDate , $endDate,$contractId,$currentWeekYear,Cheque::UNDER_COLLECTION,$company->id) ;
				CustomerInvoice::getDownPaymentsOverContracts($result,$foreignExchangeRates,$mainFunctionalCurrency,MoneyReceived::CHEQUE,'actual_collection_date',$startDate , $endDate,$contractId,$currentWeekYear,Cheque::COLLECTED,$company->id);
				CustomerInvoice::getDownPaymentsOverContracts($result,$foreignExchangeRates,$mainFunctionalCurrency ,MoneyReceived::INCOMING_TRANSFER,'receiving_date',$startDate , $endDate,$contractId,$currentWeekYear,null,$company->id);
				CustomerInvoice::getDownPaymentsOverContracts($result,$foreignExchangeRates,$mainFunctionalCurrency , MoneyReceived::CASH_IN_BANK,'receiving_date',$startDate , $endDate,$contractId,$currentWeekYear,null,$company->id);
				CustomerInvoice::getDownPaymentsOverContracts($result,$foreignExchangeRates,$mainFunctionalCurrency , MoneyReceived::CASH_IN_SAFE,'receiving_date',$startDate , $endDate,$contractId,$currentWeekYear,null,$company->id);
			}
			
		if($contractId){
			SettlementAllocation::getSettlementAllocationPerContractAndLetterOfCreditIssuance($result ,$foreignExchangeRates,$mainFunctionalCurrency ,'due_date',$contractId,$customerId,$startDate,$endDate,$currentWeekYear,$company->id);
		}		
			
			$result['customers']['Customers Past Due Invoices'] = [];
			CustomerInvoice::getSettlementAmountUnderDateForSpecificType($result,$foreignExchangeRates,$mainFunctionalCurrency,MoneyReceived::CHEQUE,'due_date',$startDate , $endDate,$contractCode,$currentWeekYear,Cheque::IN_SAFE,$company->id);
			
			 MoneyPayment::getCashOutForMoneyTypeAtDates($result,$foreignExchangeRates,$mainFunctionalCurrency,MoneyPayment::OUTGOING_TRANSFER,'delivery_date',$company->id,$startDate,$endDate,$currentWeekYear,$contractId);
			 MoneyPayment::getCashOutForMoneyTypeAtDates($result,$foreignExchangeRates,$mainFunctionalCurrency,MoneyPayment::CASH_PAYMENT,'delivery_date',$company->id,$startDate,$endDate,$currentWeekYear,$contractId);
			 MoneyPayment::getCashOutForMoneyTypeAtDates($result,$foreignExchangeRates,$mainFunctionalCurrency,MoneyPayment::PAYABLE_CHEQUE,'actual_payment_date',$company->id,$startDate,$endDate,$currentWeekYear,$contractId,PayableCheque::PAID);
			 MoneyPayment::getCashOutForMoneyTypeAtDates($result,$foreignExchangeRates,$mainFunctionalCurrency,MoneyPayment::PAYABLE_CHEQUE,'due_date',$company->id,$startDate,$endDate,$currentWeekYear,$contractId,PayableCheque::PENDING);

			if(!$contractId){
				TimeOfDeposit::getAmountAndInterestAtDates($result,$foreignExchangeRates,$mainFunctionalCurrency,$company->id,$startDate,$endDate,$currentWeekYear);
			}
			 LetterOfGuaranteeIssuance::getCommissionAndFeesAtDates($result,$foreignExchangeRates , $mainFunctionalCurrency,'date',$company->id,$startDate,$endDate,$currentWeekYear,$contractId);
			 LetterOfGuaranteeIssuance::getCashCovers($letterOfGuaranteeModelData,$result,$foreignExchangeRates , $mainFunctionalCurrency,'renewal_date',$company->id,$startDate,$endDate,$currentWeekYear,$contractId);
			 LetterOfCreditIssuance::getCommissionAndFeesAtDates($result,$foreignExchangeRates , $mainFunctionalCurrency,'date',$company->id,$startDate,$endDate,$currentWeekYear);
			 LetterOfCreditIssuance::getRemainingLcAmountAtDates($result,$foreignExchangeRates , $mainFunctionalCurrency,$company->id,$startDate,$endDate,$currentWeekYear);
			CashExpense::getCashOutForExpenseCategoriesAtDates($result,$foreignExchangeRates,$mainFunctionalCurrency,CashExpense::OUTGOING_TRANSFER,'payment_date',$company->id,$startDate,$endDate,$currentWeekYear,$contractId,null);
			CashExpense::getCashOutForExpenseCategoriesAtDates($result,$foreignExchangeRates,$mainFunctionalCurrency,CashExpense::CASH_PAYMENT,'payment_date',$company->id,$startDate,$endDate,$currentWeekYear,$contractId,null);
			CashExpense::getCashOutForExpenseCategoriesAtDates($result,$foreignExchangeRates,$mainFunctionalCurrency,CashExpense::PAYABLE_CHEQUE,'actual_payment_date',$company->id,$startDate,$endDate,$currentWeekYear,$contractId,PayableCheque::PAID);
			CashExpense::getCashOutForExpenseCategoriesAtDates($result,$foreignExchangeRates,$mainFunctionalCurrency,CashExpense::PAYABLE_CHEQUE,'due_date',$company->id,$startDate,$endDate,$currentWeekYear,$contractId,PayableCheque::PENDING);
			$result['suppliers']['Suppliers Past Due Invoices'] = [];
			if(!$contractId){
				$result['suppliers']['Loan Past Due Installments'] = [];
			}

			$dates[$currentWeekYear] = [
				'start_date' => $startDate,
				'end_date'=>$endDate 
			];
		}
		// for customers 
		$pastDueCustomerInvoices = $this->getPastDueCustomerInvoices('CustomerInvoice',$currency,$company->id,$contractCode);
		// $excludeIds = $pastDueCustomerInvoices->where('net_balance_until_date','<=',0)->pluck('id')->toArray() ;
		$customerDueInvoices=json_decode(json_encode(DB::table('weekly_cashflow_custom_due_invoices')->where('weekly_cashflow_custom_due_invoices.company_id',$company->id)
		->where('invoice_type','CustomerInvoice')
		->where('cashflow_report_id',$cashflowReportId)
		->where('is_contract',$isContract)
		->when($contractCode,function($query) use($contractCode){
						$query->join('customer_invoices','customer_invoices.id','=','weekly_cashflow_custom_due_invoices.invoice_id')
						->where('customer_invoices.contract_code',$contractCode);
		})
		// ->whereNotIn('invoice_id',$excludeIds)
		->groupBy('week_start_date')->selectRaw('week_start_date,sum(amount) as amount')->get()),true);
		
		
		// for suppliers 
		$pastDueSupplierInvoices = $isContract ? $pastDueSupplierInvoicesForContracts->toArray() : $this->getPastDueCustomerInvoices('SupplierInvoice',$currency,$company->id,$contractCode);
		$supplierContractCodes = $pastDueSupplierInvoicesForContracts->pluck('contract_code')->toArray();
		$currentContractCode = $isContract ? $supplierContractCodes : [$contractCode];
		$supplierDueInvoices=  json_decode(json_encode(DB::table('weekly_cashflow_custom_due_invoices')->where('weekly_cashflow_custom_due_invoices.company_id',$company->id)
		->where('invoice_type','SupplierInvoice')
		->where('cashflow_report_id',$cashflowReportId)
		->where('is_contract',$isContract)
		->when($contractCode,function($query) use($currentContractCode){
			$query->join('supplier_invoices','supplier_invoices.id','=','weekly_cashflow_custom_due_invoices.invoice_id')
			->where('supplier_invoices.contract_code',$currentContractCode);
			})
		->groupBy('week_start_date')->selectRaw('week_start_date,sum(amount) as amount')->get()),true);
		$isContract ? SupplierInvoice::getForecastedProjectPayment($result ,$startDate , $endDate,$currency,$company->id,$datesWithWeekNumber,$contractId) : [];
		
		// for loans 
		$pastDueInstallments = $this->getPastDueLoanSchedules($currency,$company->id);
		// $excludeIds = $pastDueInstallments->where('net_balance_until_date','<=',0)->pluck('id')->toArray() ;
		$pastDueLoanInstallments=json_decode(json_encode(DB::table('weekly_cashflow_custom_past_due_schedules')
		->where('company_id',$company->id)
		
		// ->whereNotIn('loan_schedule_id',$excludeIds)
		->groupBy('week_start_date')->selectRaw('week_start_date,sum(amount) as amount')->get()),true);
		
		$totalCashInFlowArray = $result['customers'][__('Total Cash Inflow')]['total'] ?? [];
		$totalCashInFlowArray = $this->mergeTotal($totalCashInFlowArray,$customerDueInvoices,$datesWithWeekNumber);
		$totalCashOutFlowArray = $this->sumAllTotalKeys( $result['suppliers']??[],$result['cash_expenses']??[]  , $datesWithWeekNumber);
	
		
		$totalCashOutFlowArray = $this->mergeTotal($totalCashOutFlowArray,$supplierDueInvoices,$datesWithWeekNumber,true);
		
		$totalCashOutFlowArray = $this->mergeTotal($totalCashOutFlowArray,$pastDueLoanInstallments,$datesWithWeekNumber);
		$result['customers'][__('Total Cash Inflow')]['total'] = $totalCashInFlowArray ;
		$outProjection = $result['cash_expenses'][__('Projected Other Cash Out Items')] ?? [];
		unset($result['cash_expenses'][__('Projected Other Cash Out Items')]);
		$result['cash_expenses'][__('Projected Other Cash Out Items')] =$outProjection;
		$result['cash_expenses'][__('Total Cash Outflow')]['total'] = $totalCashOutFlowArray;
		// $result['cash_expenses'][__('Total Cash Outflow')]['total']['total_of_total'] = array_sum($totalCashOutFlowArray);
		$netCash = HArr::subtractAtDates([$totalCashInFlowArray,$totalCashOutFlowArray] , array_merge(array_keys($totalCashInFlowArray),array_keys($totalCashOutFlowArray))) ;
	
		$result['cash_expenses'][__('Net Cash (+/-)')]['total'] = $netCash;
		// $result['cash_expenses'][__('Net Cash (+/-)')]['total']['total_of_total'] = array_sum($netCash) ;
		// $result['cash_expenses'][__('Accumulated Net Cash (+/-)')]['total'] = [];
		
		$result['cash_expenses'][__('Accumulated Net Cash (+/-)')]['total'] = $this->formatAccumulatedNetCash($netCash,$weeks);
		$orderByKeys = [
			'Cash Payments',
			'Outgoing Transfers',
			'Paid Payable Cheques',
			'Under Payment Payable Cheques',
			'Suppliers Invoices',
			'Suppliers Past Due Invoices',
			'Loan Past Due Installments',
			'Forecasted Suppliers Contract Payments'
		];
	
		$result['suppliers'] = collect($result['suppliers'])->sortBy(function($value,$key) use ($orderByKeys){
			return array_search($key, $orderByKeys);
		})->toArray();
		if($returnResultAsArray){
			return [
				'result'=>$result , 
				'dates'=>$dates,
				'contractCode'=>$contractCode,
					'pastDueCustomerInvoices'=>[$currency=>$pastDueCustomerInvoices],
				'currencyName'=>$currencyName,
				'reportInterval'=>$reportInterval,
				'weeks'=>$weeks,
				'pastDueSupplierInvoices'=>$pastDueSupplierInvoices,
				'pastDueInstallments'=>$pastDueInstallments
			] ;
		}
		$allCurrencies = [$currency];
		$finalResult[$currency] = $result;
		$pastDueCustomerInvoicesPerCurrency[$currency]=$pastDueCustomerInvoices;
		$customerDueInvoicesPerCurrency[$currency] = $customerDueInvoices;
		$reportData = [
			'weeks'=>$weeks,
			'allCurrencies'=>$allCurrencies,
			'finalResult'=>$finalResult,
			'dates'=>$dates,
			
			'pastDueCustomerInvoices'=>$pastDueCustomerInvoicesPerCurrency,
			
			'customerDueInvoices'=>$customerDueInvoicesPerCurrency,
			'pastDueSupplierInvoices'=>$pastDueSupplierInvoices,
			'supplierDueInvoices'=>$supplierDueInvoices,
			'pastDueInstallments'=>$pastDueInstallments,
			'pastDueLoanInstallments'=>$pastDueLoanInstallments,
		//	'letterOfGuaranteeModelData'=>$letterOfGuaranteeModelData,
			'months'=>$months ,
			'days'=>$days,
			'reportInterval'=>$reportInterval,
			
			'noRowHeaders'=>$noRowHeaders,
			'title'=>$title
		] ;
		
		
			if($saveReport){
				$cashFlowReport = CashflowReport::create([
					'is_contract'=>$isContract,
					'report_name'=>$request->get('report_name'),
					'report_data'=>json_encode($reportData),
					'start_date'=>$formStartDate,
					'end_date'=>$formEndDate,
					'report_interval'=>$reportInterval,
					'company_id'=>$company->id
				]);
				$routeParams = ['company'=>$company->id,'returnResultAsArray'=>'view',$cashFlowReport->id] ;
				if($isContract){
					$routeParams['contract_id'] = $contractId;
				}
				return redirect()->route($redirectRouteName,$routeParams);
			}
		return view('admin.reports.contract-cash-flow-report',array_merge($reportData,['currencyName'=>$currencyName,'contractCode'=>$contractCode,'letterOfGuaranteeModelData'=>$letterOfGuaranteeModelData]));
	}
	public function formatAccumulatedNetCash(array $netCashes,array $weeks)
	{
		$currentAccumulated = 0 ;
		$result = [];

		foreach($weeks as $week => $weekNumber){
			$currentAccumulated +=  $netCashes[$week] ?? 0;
			$result[$week] = $currentAccumulated ;
		}
		return $result ;
	}
	public function mergeTotal(array $totals , $arrayOfItems,array $datesWithWeekNumber,$debug = false ):array 
	{
		foreach($arrayOfItems as $itemArr){
			$dateFormatted = $datesWithWeekNumber[$itemArr['week_start_date']]??null;
		
			if(is_null($dateFormatted)){
				continue;
			}
			$currentAmount = $itemArr['amount'];
			$totals[$dateFormatted] = isset($totals[$dateFormatted]) ? $totals[$dateFormatted] + $currentAmount : $currentAmount;
		}
		return $totals;
	}
	protected function mergeYearWithWeek(array $weeks , Carbon $startDate ):array{
		$newWeeks = [];
		if(!count($weeks)){
			return [];
		}
		foreach($weeks as $date => $weekNumber){
			$currentDate =Carbon::make($date);
				$year = $currentDate->year ;
				if($currentDate->greaterThanOrEqualTo($startDate)){
					$newWeeks[$weekNumber.'-'.$year] = $weekNumber; 
				}
			
		}
		return $newWeeks;
		
	}
	
	protected function getDateWithWeakNumber(array $weeks , Carbon $startDate ):array{
		
		$newWeeks = [];
		if(!count($weeks)){
			return [];
		}
		foreach($weeks as $date => $weekNumber){
			$currentDate =Carbon::make($date);
				$year = $currentDate->year ;
				if($currentDate->greaterThanOrEqualTo($startDate)){
					$newWeeks[$date] =  $weekNumber.'-'.$year; 
				}
			
		}
		return $newWeeks;
		
	}
	
	
	
	
	
	


	
	
	public function getPastDueCustomerInvoices(string $invoiceType,string $currency , int $companyId , string $contractCode = null ){
		$fullClassName = '\App\Models\\'.$invoiceType;

		$items  = $fullClassName::where('company_id',$companyId)
		->where('net_balance','>',0)
		->whereIn('invoice_status',['past_due','partially_collected_and_past_due'])
		->where('currency',$currency)
		->where('invoice_due_date','<',now()->format('Y-m-d'))
		->when($contractCode , function($query) use($contractCode,$invoiceType) {
			$query->where('contract_code',$contractCode);
		})
		->orderBy('invoice_due_date')
		->get()->toArray() ;
		
		return $items;
	}
	public function getPastDueLoanSchedules(string $currency , int $companyId  ){
		$items  = LoanSchedule::where('loan_schedules.company_id',$companyId)
		->where('remaining','>',0)
		->join('medium_term_loans','medium_term_loans.id','=','loan_schedules.medium_term_loan_id')
		->where('medium_term_loans.currency',$currency)
		->whereIn('loan_schedules.status',['past_due','partially_collected_and_past_due'])
		->where('date','<',now()->format('Y-m-d'))
		->orderBy('date')
		->selectRaw('loan_schedules.*,medium_term_loans.currency')->get()->toArray() ;
		return $items;
	}
	
	
	
	
	
	// protected function getCashExpensesAtDates(int $companyId , string $startDate , string $endDate,string $currency,int $cashExpenseCategoryNameId) 
	// {
	// 	return DB::table('cash_expenses')->where('company_id',$companyId)->whereBetween('payment_date',[$startDate,$endDate])->where('currency',$currency)->where('cash_expense_category_name_id',$cashExpenseCategoryNameId)->sum('paid_amount');
	// }
	public function adjustCustomerDueInvoices(Request $request,Company $company){
		$invoiceType = $request->get('invoiceType');
		$currencyName = $request->get('currency_name');
		$contractCode = $request->get('contract_code');
		$isContract = $request->get('is_contract');
		$cashflowReportId = $request->get('cashflow_report_id');
	
		foreach($request->get('customer_invoice_id',[]) as $customerInvoiceId){
			$weekStartDate = $request->input('week_start_date.'.$customerInvoiceId);
			$percentage = $request->input('percentage.'.$customerInvoiceId);
			$invoiceAmount = $request->input('invoice_amount.'.$customerInvoiceId);
			$amount = $percentage/100  * $invoiceAmount;
			$first = DB::table('weekly_cashflow_custom_due_invoices')
			->where('company_id',$company->id)
			->where('invoice_id',$customerInvoiceId)
			->where('is_contract',$isContract)
			->where('cashflow_report_id',$cashflowReportId)
			->where('invoice_type',$invoiceType)->first();
			$data = [
				'company_id'=>$company->id ,
				'invoice_id'=>$customerInvoiceId,
				'invoice_type'=>$invoiceType,
				'week_start_date'=>$weekStartDate,
				'percentage'=>$percentage,
				'amount'=>$amount,
				'company_id'=>$company->id ,
				'cashflow_report_id'=>$cashflowReportId,
				'is_contract'=>$isContract,
			] ;
			if($first){
				DB::table('weekly_cashflow_custom_due_invoices')
				->where('company_id',$company->id)
				->where('invoice_id',$customerInvoiceId)
				->where('cashflow_report_id',$cashflowReportId)
				->where('is_contract',$isContract)
				->where('invoice_type',$invoiceType)->update($data);
			}else{
				DB::table('weekly_cashflow_custom_due_invoices')->insert($data);
			}
			
		}
		$this->refreshDueInvoicesAndSettlements($company,$request,$currencyName,$isContract,$contractCode);
		// $excludeIds = $pastDueInstallments->where('net_balance_until_date','<=',0)->pluck('id')->toArray() ;
		// ->whereNotIn('loan_schedule_id',$excludeIds)

			// 'pastDueCustomerInvoices'=>$pastDueCustomerInvoicesPerCurrency,
			// 'customerDueInvoices'=>$customerDueInvoicesPerCurrency,
			// 'pastDueSupplierInvoices'=>$pastDueSupplierInvoices,
			// 'supplierDueInvoices'=>$supplierDueInvoices,
			// 'pastDueInstallments'=>$pastDueInstallments,
			// 'pastDueLoanInstallments'=>$pastDueLoanInstallments,
			
		
	
		return response()->json([
			'status'=>true ,
			'message'=>'',
			'reloadCurrentPage'=>true 
		]);
	}
	public function refreshDueInvoicesAndSettlements(Company $company , Request $request , string $currency , bool $isContract , string $contractCode = null  )
	{
		
		
		
	
		$cashflowReportId = $request->get('cashFlowReportId');
		$model  = $cashflowReportId ? CashFlowReport::find($cashflowReportId) : $company;
		// for loans 
		if($cashflowReportId && $cashflowReportId > 0){
			$oldReportData = json_decode($model->report_data,true);
			$oldReportData ? extract($oldReportData) : null;
			// for customers 
			$pastDueCustomerInvoices = $this->getPastDueCustomerInvoices('CustomerInvoice',$currency,$company->id,$contractCode);
			// $excludeIds = $pastDueCustomerInvoices->where('net_balance_until_date','<=',0)->pluck('id')->toArray() ;
			$customerDueInvoices=json_decode(json_encode(DB::table('weekly_cashflow_custom_due_invoices')->where('company_id',$company->id)
			->where('invoice_type','CustomerInvoice')
			->where('cashflow_report_id',$cashflowReportId)
			->where('is_contract',$isContract)
			// ->whereNotIn('invoice_id',$excludeIds)
			->groupBy('week_start_date')->selectRaw('week_start_date,sum(amount) as amount')->get()),true);
		
		// for suppliers 
			$pastDueSupplierInvoices = $this->getPastDueCustomerInvoices('SupplierInvoice',$currency,$company->id,$contractCode);
			$supplierDueInvoices=json_decode(json_encode(DB::table('weekly_cashflow_custom_due_invoices')->where('company_id',$company->id)
			->where('invoice_type','SupplierInvoice')
			->where('cashflow_report_id',$cashflowReportId)
			->where('is_contract',$isContract)
			// ->whereNotIn('invoice_id',$excludeIds)
			->groupBy('week_start_date')->selectRaw('week_start_date,sum(amount) as amount')->get()),true);
		
			$pastDueInstallments = $this->getPastDueLoanSchedules($currency,$company->id);
			$pastDueLoanInstallments=json_decode(json_encode(DB::table('weekly_cashflow_custom_past_due_schedules')->where('company_id',$company->id)
			->groupBy('week_start_date')->selectRaw('week_start_date,sum(amount) as amount')->get()),true);
			$pastDueCustomerInvoicesPerCurrency[$currency] = $pastDueCustomerInvoices;
			$customerDueInvoicesPerCurrency[$currency] = $customerDueInvoices;
		
			$oldReportData['pastDueCustomerInvoices'] =$pastDueCustomerInvoicesPerCurrency ;
			$oldReportData['customerDueInvoices']=$customerDueInvoicesPerCurrency;
			$oldReportData['pastDueSupplierInvoices']=$pastDueSupplierInvoices;
			$oldReportData['supplierDueInvoices']=$supplierDueInvoices;
			$oldReportData['pastDueInstallments']=$pastDueInstallments;
			$oldReportData['pastDueLoanInstallments']=$pastDueLoanInstallments;
		
			$model->update([
				'report_data'=>json_encode($oldReportData)
			]);
		}
	}
	
	
	public function adjustLoanPastDueInstallments(Request $request,Company $company ){
		$currencyName = $request->get('currency_name');
		$isContract = $request->get('is_contract');
		$contractCode = $request->get('contract_code');
		// $contractCode = 
		foreach($request->get('loan_schedule_id',[]) as $loanScheduleId){
			$weekStartDate = $request->input('week_start_date.'.$loanScheduleId);
			$percentage = $request->input('percentage.'.$loanScheduleId);
			$invoiceAmount = $request->input('invoice_amount.'.$loanScheduleId);
			$amount = $percentage/100  * $invoiceAmount;
			$first = DB::table('weekly_cashflow_custom_past_due_schedules')
			->where('company_id',$company->id)
			->where('is_contract',$isContract)
			->where('loan_schedule_id',$loanScheduleId)
			->first();
			$data = [
				'is_contract'=>$isContract,
				'company_id'=>$company->id ,
				'loan_schedule_id'=>$loanScheduleId,
				'week_start_date'=>$weekStartDate,
				'percentage'=>$percentage,
				'amount'=>$amount,
				'company_id'=>$company->id 
			] ;
			if($first){
				DB::table('weekly_cashflow_custom_past_due_schedules')
				->where('company_id',$company->id)
				->where('is_contract',$isContract)
				->where('loan_schedule_id',$loanScheduleId)
				->update($data);
			}else{
				DB::table('weekly_cashflow_custom_past_due_schedules')->insert($data);
			}
			$this->refreshDueInvoicesAndSettlements($company,$request,$currencyName,$isContract,$contractCode);
		}
		return response()->json([
			'status'=>true ,
			'message'=>'',
			'reloadCurrentPage'=>true 
		]);
	}
	
	public function saveProjection(Request $request , Company $company )
	{
		$projectionType = $request->get('type');
		$dates = array_keys((array)json_decode($request->input('dates.0')));
		$cashflowReportId = $request->get('cashFlowReportId');
		$isContract = $request->get('is_contract');
		$model  = $cashflowReportId ? CashFlowReport::find($cashflowReportId) : $company;
		$model->cashProjects()->where('is_contract',$isContract)->where('type',$projectionType)->delete();
		foreach($request->get('projection-'.$projectionType.'id') as $projectionArr){
			$amounts = $projectionArr['amounts'];
			$amounts = array_combine($dates,$amounts);
			$model->cashProjects()->create([
				'is_contract'=>$isContract,
				'name'=>$projectionArr['name'],
				'type'=>$projectionType,
				'amounts'=>$amounts,
				'cashflow_report_id'=>$cashflowReportId,
				'company_id'=>$company->id ,
			]);
		}
		// $request->merge([
		// 	'reset_report'=>0
		// ]);			
		
		if($cashflowReportId){
	
			$newResult =[];
			CashExpense::getProjectionOtherCashOut($newResult ,$company,$cashflowReportId,$isContract) ;
			CustomerInvoice::getProjectionOtherCashIn($newResult ,$company,$cashflowReportId,$isContract) ;
			$oldReportData = json_decode($model->report_data,true);
			extract($oldReportData);
			foreach($allCurrencies as $currencyName){
				$oldReportData['finalResult'][$currencyName]['customers']['Projected Other Cash In Items'] =$newResult['customers']['Projected Other Cash In Items']??[] ;
				$oldReportData['finalResult'][$currencyName]['cash_expenses']['Projected Other Cash Out Items'] =$newResult['customers']['Projected Other Cash Out Items']??[] ;
			}
			$model->update([
				'report_data'=>json_encode($oldReportData)
			]);
			return redirect()->route($redirectRouteName,['company'=>$company->id,'cashflowReport'=>$model->id,'returnResultAsArray'=>'view']);
			
		}
		return redirect()->back()->with('without_resetting',1);
			
	}

	public function destroy(Request $request, Company $company,CashflowReport $cashflowReport){
		$viewRouteName = $cashflowReport->is_contract ? 'view.contract.cashflow.report' :'view.cashflow.report';
		$cashflowReport->cashProjects()->delete();
		DB::table('weekly_cashflow_custom_due_invoices')
		->where('company_id',$company->id)
		->where('cashflow_report_id',$cashflowReport->id)->delete();
		$cashflowReport->delete();
		return redirect()->route($viewRouteName,['company'=>$company->id]);
	}
	protected function sumAllTotalKeys(array $items,array $items2,array $datesWithWeekNumber){
		
		$totals=[];
		foreach(array_flip($datesWithWeekNumber) as $week=>$date){
			foreach($items as $subItemName => $itemArr){
				$currentTotal = $itemArr['total'][$week]??0 ;
				$totals[$week]= isset($totals[$week]) ? $totals[$week] + $currentTotal:$currentTotal ;
			}
			foreach($items2 as $subItemName => $itemArr){
				$currentTotal = $itemArr['total'][$week]??0 ;
				$totals[$week]= isset($totals[$week]) ? $totals[$week] + $currentTotal:$currentTotal ;
			}
		}
		return $totals;
		
	}
}
