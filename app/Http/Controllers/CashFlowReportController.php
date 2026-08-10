<?php

namespace App\Http\Controllers;

use App\Helpers\HArr;
use App\Helpers\HDate;
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
use App\Services\Reports\CashFlowCompanyPeriodBatchLoader;
use App\Services\Reports\CashFlowContractDetailPeriodBatchLoader;
use App\Traits\GeneralFunctions;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
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

	/**
	 * Shared timeline axis for consolidated cash flow report.
	 *
	 * @return array<string, mixed>|RedirectResponse
	 */
	public function buildSharedTimelineContext(Company $company, Request $request): array|RedirectResponse
	{
		$defaultStartDate = $request->get('cash_start_date', now()->format('Y-m-d'));
		$defaultEndDate = $request->get('cash_end_date', now()->addMonth()->format('Y-m-d'));
		$formStartDate = Carbon::make($request->get('start_date', $defaultStartDate))->format('Y-m-d');
		$formEndDate = Carbon::make($request->get('end_date', $defaultEndDate))->format('Y-m-d');
		if (! now()->between($formStartDate, $formEndDate)) {
			return redirect()->back()->with('fail', __('Kindly the date of Today must be included within the report duration'));
		}

		$reportInterval = $request->get('report_interval');
		if (empty($reportInterval) || ! in_array($reportInterval, ['daily', 'weekly', 'monthly'], true)) {
			$reportInterval = 'monthly';
		}

		$startDate = Carbon::make($request->get('start_date', $defaultStartDate))->format('Y-m-d');
		$endDate = Carbon::make($request->get('end_date', $defaultEndDate))->format('Y-m-d');
		$year = explode('-', $startDate)[0];

		$datesWithWeeks = [];
		if ($reportInterval === 'weekly') {
			$datesWithWeeks = HDate::getWeekNumberBetweenDates($year, Carbon::make($endDate));
		} elseif ($reportInterval === 'monthly') {
			$datesWithWeeks = HDate::getMonthNumberBetweenDates($year, Carbon::make($endDate));
		} elseif ($reportInterval === 'daily') {
			$datesWithWeeks = HDate::getDayNumberBetweenDates($year, Carbon::make($endDate));
		}

		$weeks = $this->mergeYearWithWeek($datesWithWeeks, Carbon::make($startDate));
		$datesWithWeekNumber = $this->getDateWithWeakNumber($datesWithWeeks, Carbon::make($startDate));
		$foreignExchangeRates = ForeignExchangeRate::where('company_id', $company->id)->get();
		$firstIndex = array_key_first($weeks);
		$lastIndex = array_key_last($weeks);
		$dates = [];

		foreach ($weeks as $currentWeekYear => $week) {
			$currentYear = explode('-', (string) $currentWeekYear)[1];
			if ($currentWeekYear === $firstIndex) {
				$periodStart = $startDate;
				$periodEnd = HDate::getMinDateOfWeek($datesWithWeeks, $week, $currentYear)['end_date'];
			} elseif ($currentWeekYear === $lastIndex) {
				$periodStart = HDate::getMinDateOfWeek($datesWithWeeks, $week, $currentYear)['start_date'];
				$periodEnd = $request->get('end_date', $defaultEndDate);
			} else {
				$rangedWeeks = HDate::getMinDateOfWeek($datesWithWeeks, $week, $currentYear);
				$periodStart = $rangedWeeks['start_date'];
				$periodEnd = $rangedWeeks['end_date'];
			}
			$dates[$currentWeekYear] = [
				'start_date' => $periodStart,
				'end_date' => $periodEnd,
			];
		}

		return [
			'mainFunctionalCurrency' => $company->getMainFunctionalCurrency(),
			'reportInterval' => $reportInterval,
			'formStartDate' => $formStartDate,
			'formEndDate' => $formEndDate,
			'defaultStartDate' => $defaultStartDate,
			'defaultEndDate' => $defaultEndDate,
			'startDate' => $startDate,
			'endDate' => $endDate,
			'weeks' => $weeks,
			'dates' => $dates,
			'datesWithWeekNumber' => $datesWithWeekNumber,
			'datesWithWeeks' => $datesWithWeeks,
			'foreignExchangeRates' => $foreignExchangeRates,
			'firstIndex' => $firstIndex,
			'lastIndex' => $lastIndex,
			'months' => generateDatesBetweenTwoDates(Carbon::make($formStartDate), Carbon::make($formEndDate)),
			'days' => generateDatesBetweenTwoDates(Carbon::make($formStartDate), Carbon::make($formEndDate), 'addDay'),
		];
	}
	
	public function result(Company $company , Request $request, bool $returnResultAsArray = false ,  ?CashFlowReport $cashflowReport= null   , $defaultCashFlowId = 0 ){
		$saveReport = $request->has('save_report');
		$resetReport = $request->has('reset_report') && $request->get('reset_report');
		$contractId = $request->get('contract_id')	 ;
		$contract = Contract::find($contractId);
		
		/**
		 * @var Contract|null $contract 
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
			// Reports saved before these keys were serialised replay without
			// them, so default rather than letting the view hit an undefined
			// variable.
			return view('admin.reports.contract-cash-flow-report',array_merge($reportData,[
				'cashflowReport'=>$cashflowReport,
				'currencyName'=>$currencyName,
				'contractCode'=>$contractCode,
				'letterOfGuaranteeModelData'=>$reportData['letterOfGuaranteeModelData'] ?? [],
				'incomingTransferModelData'=>$reportData['incomingTransferModelData'] ?? [],
				'crossCurrencyNotes'=>$reportData['crossCurrencyNotes'] ?? [],
			]));
		}
			$mainFunctionalCurrency= $company->getMainFunctionalCurrency();
		$isContract = (bool)$contract ;
		$currencyName = $isContract ? $contract->getCurrency(): $request->get('currency',$mainFunctionalCurrency);
		$reportInterval = $request->get('report_interval');
		if (empty($reportInterval) || !in_array($reportInterval, ['daily', 'weekly', 'monthly'], true)) {
			$reportInterval = 'monthly';
			// return redirect()->back()->with('fail', __('Please select Report Interval.'))->withInput($request->only(['report_interval', 'start_date', 'end_date', 'contract_id', 'partner_id']));
		}
		$customerContractId = $contractId ;
		
		$poAllocations = PoAllocation::withSupplierPurchaseOrderDetails()
		->where('po_allocations.contract_id',$customerContractId)
		->get();
		$defaultStartDate = $request->get('cash_start_date',now()->format('Y-m-d'));
		$defaultEndDate = $request->get('cash_end_date',now()->addMonth()->format('Y-m-d'));
		$formStartDate =Carbon::make($request->get('start_date',$defaultStartDate))->format('Y-m-d'); 
		$formEndDate =Carbon::make($request->get('end_date',$defaultEndDate))->format('Y-m-d');
		// This rule used to apply to BOTH the Company Cash Flow and the
		// Contract Cash Flow report, since they share this same result()
		// method. For a contract that finished months ago, that forced the
		// user to artificially stretch the date range just to include today,
		// producing a report padded with extra all-zero columns purely to
		// satisfy the check. Scoped to $isContract === false only — the
		// Company Cash Flow report (a live cash-position view, whose opening
		// "Cash & Banks Balance" row is only meaningful relative to today)
		// keeps the original rule unchanged; a Contract Cash Flow report can
		// now be run over any historical period regardless of today's date.
		if(!$isContract && !now()->between($formStartDate,$formEndDate)){
			return redirect()->back()->with('fail',__('Kindly the date of Today must be included within the report duration'));
		}

		$title = $request->has('title') ? $request->get('title') : __('Company Cash Flow') . ' [ ' . $reportInterval . ' ]' ;
		
		// $reportInterval = 'daily';
		$result = [];
		$letterOfGuaranteeModelData = [];
		$incomingTransferModelData = [];
		$crossCurrencyNotes = [];
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
		// Pre-seeded so the rows (and their "View" buttons) render even when
		// empty, the same way 'Customers Past Due Invoices' already is above.
		// Must stay ABOVE CashExpense::getProjectionOtherCashOut(), which is
		// the first thing to write into cash_expenses.
		$result['suppliers'] = [
			'Suppliers Past Due Invoices'=>[],
		];
		if(! $contractId){
			// Loan installments are company-wide, not tied to any single
			// contract, so this row is deliberately excluded from the
			// Contract Cash Flow report.
			$result['suppliers']['Loan Past Due Installments'] = [];
		}
		$result['cash_expenses'] = [];
		$noRowHeaders =  $reportInterval == 'weekly' ? 3 : 1 ;
		$months = generateDatesBetweenTwoDates(Carbon::make($formStartDate),Carbon::make($formEndDate)); 
		$days = generateDatesBetweenTwoDates(Carbon::make($formStartDate),Carbon::make($formEndDate),'addDay'); 
		$startDate = Carbon::make($request->get('start_date',$defaultStartDate))->format('Y-m-d');
		$currency = $request->get('currency');
		
		// ⚠️ Bug fix: the else branch used to fire whenever a currency WAS
		// supplied (`is_null($currency) && $contract` being false), silently
		// overwriting the user's chosen currency with the main functional
		// one. Only fall back when nothing was requested.
		if (is_null($currency)) {
			$currency = $contract ? $contract->getCurrency() : $company->getMainFunctionalCurrency();
		}
		$year = explode('-',$startDate)[0];
		$endDate  = Carbon::make($request->get('end_date',$defaultEndDate))->format('Y-m-d');
		$redirectRouteName = $this->getRedirectRoute($isContract);
	
		$datesWithWeeks = [];
		if($reportInterval == 'weekly'){
			$datesWithWeeks = 	HDate::getWeekNumberBetweenDates($year , Carbon::make($endDate)) ;
		}
		elseif($reportInterval == 'monthly'){
			$datesWithWeeks = 	HDate::getMonthNumberBetweenDates($year , Carbon::make($endDate)) ;
		}
		elseif($reportInterval == 'daily'){
			$datesWithWeeks = 	HDate::getDayNumberBetweenDates($year , Carbon::make($endDate)) ;
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
		      CustomerInvoice::getCashAndBankBalanceAtDate($result ,$foreignExchangeRates,$mainFunctionalCurrency,$startDate ,array_keys($weeks)[0],$company->id,$currency) ;
			  LoanSchedule::getLoanInstallmentsAtDates($result,$foreignExchangeRates,$mainFunctionalCurrency,$company->id,$datesWithWeekNumber,$endDate);
		}
		
		  CustomerInvoice::getProjectionOtherCashIn($result ,$company,$cashflowReportId,$isContract) ;
		  /**
		   * ! start postponed
		   */
		  CustomerInvoice::getForecastedProjectCollection($result ,$startDate , $endDate,$currency,$company->id,$datesWithWeekNumber,$contractId,$foreignExchangeRates,$mainFunctionalCurrency) ;
		   SupplierInvoice::getForecastedProjectCollection($result ,$startDate , $endDate,$currency,$company->id,$datesWithWeekNumber,$contractId,$foreignExchangeRates,$mainFunctionalCurrency,$poAllocations) ;
		
		 /**
		   * ! end postponed
		   */
		  
		  CustomerInvoice::getCustomerInvoicesUnderCollectionAtDatesForContracts($result,$company->id,$contractCode,$datesWithWeekNumber,$endDate,$currency,$mainFunctionalCurrency);

		  $isContract ? SupplierInvoice::getSupplierInvoicesForPoUnderCollectionAtDates($result,$company->id,$datesWithWeekNumber,$startDate,$endDate,$poAllocations,$pastDueSupplierInvoicesForContracts) : SupplierInvoice::getSupplierInvoicesUnderCollectionAtDates($result,$company->id,$datesWithWeekNumber,$startDate,$endDate,$currency,$mainFunctionalCurrency);
	
		/**
		 * Per-period movements. These used to be gathered by looping over
		 * every week/month/day column and firing ~20 queries per column —
		 * so a 52-week report ran over a thousand queries. The batch
		 * loaders below run one query per category for the WHOLE period
		 * and bucket each row into its column in PHP, which is where the
		 * Contract report also picks up the movements it can only reach
		 * through po_allocations (supplier payments and LCs settled
		 * against an allocated PO).
		 */
		$dates = $this->buildPeriodDatesMap(
			$weeks,
			$datesWithWeeks,
			$startDate,
			$endDate,
			$request->get('end_date', $defaultEndDate),
		);

		$periodStart = $startDate;
		$periodEnd = $endDate;

		ForeignExchangeRate::beginRequestMemo();

		try {
			if (! $contractId) {
				CashFlowCompanyPeriodBatchLoader::apply(
					$result,
					$foreignExchangeRates,
					$mainFunctionalCurrency,
					$company->id,
					$periodStart,
					$periodEnd,
					$dates,
					$letterOfGuaranteeModelData,
					$currency,
					$incomingTransferModelData,
					$crossCurrencyNotes,
				);
			} else {
				CashFlowContractDetailPeriodBatchLoader::apply(
					$result,
					$letterOfGuaranteeModelData,
					$foreignExchangeRates,
					$mainFunctionalCurrency,
					$company->id,
					(string) $contractCode,
					(int) $contractId,
					(int) $customerId,
					$periodStart,
					$periodEnd,
					$dates,
					$incomingTransferModelData,
					$poAllocations,
				);
			}
		} finally {
			ForeignExchangeRate::endRequestMemo();
		}
		// for customers 
		$pastDueCustomerInvoices = $this->getPastDueCustomerInvoices('CustomerInvoice',$currency,$company->id,$contractCode,$mainFunctionalCurrency);
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
		$pastDueSupplierInvoices = $isContract ? $pastDueSupplierInvoicesForContracts->toArray() : $this->getPastDueCustomerInvoices('SupplierInvoice',$currency,$company->id,$contractCode,$mainFunctionalCurrency);
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

		// for loans
		// Loan installments are company-wide, not tied to any single
		// contract, so this list is deliberately empty on Contract Cash Flow.
		$pastDueInstallments = $isContract ? [] : $this->getPastDueLoanSchedules($currency,$company->id,$mainFunctionalCurrency,$foreignExchangeRates);
		$pastDueLoanInstallments=json_decode(json_encode(DB::table('weekly_cashflow_custom_past_due_schedules')
		->where('company_id',$company->id)
		
		->groupBy('week_start_date')->selectRaw('week_start_date,sum(amount) as amount')->get()),true);
		
		// Single source of truth for the totals/Net Cash rows — the same
		// method the Consolidated and batched Contract reports call, so
		// all three can never drift apart again.
		$this->finalizeContractCashFlowTotals(
			$result,
			$company,
			$currency,
			$contractCode,
			$datesWithWeekNumber,
			$weeks,
			$cashflowReportId,
			$isContract,
			$contractId ? (int) $contractId : null,
			$formStartDate,
			$formEndDate,
			$pastDueSupplierInvoicesForContracts,
			$customerDueInvoices,
			$supplierDueInvoices,
			$pastDueLoanInstallments,
			$foreignExchangeRates,
			$mainFunctionalCurrency,
		);

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
			// Kept in $reportData (rather than only passed to the view) so a
			// SAVED report replays its breakdown popups too — they used to be
			// blank on replay because this key was never serialised.
			'letterOfGuaranteeModelData'=>$letterOfGuaranteeModelData,
			'incomingTransferModelData'=>$incomingTransferModelData,
			'crossCurrencyNotes'=>$crossCurrencyNotes,
			'months'=>$months ,
			'days'=>$days,
			'reportInterval'=>$reportInterval,
			'report_interval'=>$reportInterval,
			
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
				$routeParams = ['company'=>$company->id,'report_interval'=>$reportInterval,'returnResultAsArray'=>'view','cashflowReport'=>$cashFlowReport->id,'start_date'=>$formStartDate,'end_date'=>$formEndDate] ;
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

	public function finalizeContractCashFlowTotals(
		array &$result,
		Company $company,
		string $currency,
		?string $contractCode,
		array $datesWithWeekNumber,
		array $weeks,
		int $cashflowReportId = 0,
		bool $isContract = true,
		?int $contractId = null,
		string $formStartDate = '',
		string $formEndDate = '',
		$pastDueSupplierInvoicesForContracts = [],
		array $customerDueInvoices = [],
		array $supplierDueInvoices = [],
		array $pastDueLoanInstallments = [],
		$foreignExchangeRates = null,
		?string $mainFunctionalCurrency = null,
	): void {
		if ($isContract && $contractId) {
			SupplierInvoice::getForecastedProjectPayment($result, $formStartDate, $formEndDate, $currency, $company->id, $datesWithWeekNumber, $contractId, $foreignExchangeRates, $mainFunctionalCurrency);
		}

		$totalCashInFlowArray = $result['customers'][__('Total Cash Inflow')]['total'] ?? [];
		$totalCashInFlowArray = $this->mergeTotal($totalCashInFlowArray, $customerDueInvoices, $datesWithWeekNumber);
		$totalCashOutFlowArray = $this->sumAllTotalKeys($result['suppliers'] ?? [], $result['cash_expenses'] ?? [], $datesWithWeekNumber);

		$totalCashOutFlowArray = $this->mergeTotal($totalCashOutFlowArray, $supplierDueInvoices, $datesWithWeekNumber, true);
		$totalCashOutFlowArray = $this->mergeTotal($totalCashOutFlowArray, $pastDueLoanInstallments, $datesWithWeekNumber);
		$result['customers'][__('Total Cash Inflow')]['total'] = $totalCashInFlowArray;

		$outProjection = $result['cash_expenses'][__('Projected Other Cash Out Items')] ?? [];
		unset($result['cash_expenses'][__('Projected Other Cash Out Items')]);
		$result['cash_expenses'][__('Projected Other Cash Out Items')] = $outProjection;
		$result['cash_expenses'] = $this->placeNewLgCashCoverAfterLgCommissionFees($result['cash_expenses'] ?? []);
		$result['cash_expenses'][__('Total Cash Outflow')]['total'] = $totalCashOutFlowArray;

		$netCash = HArr::subtractAtDates([$totalCashInFlowArray, $totalCashOutFlowArray], array_merge(array_keys($totalCashInFlowArray), array_keys($totalCashOutFlowArray)));
		$result['cash_expenses'][__('Net Cash (+/-)')]['total'] = $netCash;
		$result['cash_expenses'][__('Accumulated Net Cash (+/-)')]['total'] = $this->formatAccumulatedNetCash($netCash, $weeks);
	}

	/**
	 * Keep Issued LG Cash Cover immediately after LGs Commission & Fees in cash_expenses.
	 *
	 * The row key was 'New Issued LG Cash Cover' while the per-week
	 * LetterOfGuaranteeIssuance::getIssuedCashCovers() built it; the batch
	 * loaders that replaced it emit 'Issued LG Cash Cover' (the same row,
	 * now also correctly scoped to category_name = New Issuance in the
	 * query itself rather than in the label).
	 */
	protected function placeNewLgCashCoverAfterLgCommissionFees(array $cashExpenses): array
	{
		$commissionKey = __('LGs Commission & Fees');
		$newLgCashCoverKey = __('Issued LG Cash Cover');

		if (!array_key_exists($newLgCashCoverKey, $cashExpenses)) {
			return $cashExpenses;
		}

		$newLgCashCover = $cashExpenses[$newLgCashCoverKey];
		unset($cashExpenses[$newLgCashCoverKey]);

		if (!array_key_exists($commissionKey, $cashExpenses)) {
			$cashExpenses[$newLgCashCoverKey] = $newLgCashCover;

			return $cashExpenses;
		}

		$reordered = [];
		foreach ($cashExpenses as $key => $value) {
			$reordered[$key] = $value;
			if ($key === $commissionKey) {
				$reordered[$newLgCashCoverKey] = $newLgCashCover;
			}
		}

		return $reordered;
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
	/**
	 * Column key => {start_date, end_date} for every period column, with
	 * the first and last columns clipped to the report's own bounds.
	 * Extracted from the old per-week loop so the batch loaders can be
	 * given the whole axis up front.
	 *
	 * @param  array<string, string|int>  $weeks
	 * @return array<string, array{start_date: string, end_date: string}>
	 */
	protected function buildPeriodDatesMap(
		array $weeks,
		array $datesWithWeeks,
		string $reportStartDate,
		string $reportEndDate,
		?string $requestEndDate = null,
	): array {
		$dates = [];
		$firstIndex = array_key_first($weeks);
		$lastIndex = array_key_last($weeks);

		foreach ($weeks as $currentWeekYear => $week) {
			$currentYear = explode('-', (string) $currentWeekYear)[1];
			if ($currentWeekYear === $firstIndex) {
				$periodStart = $reportStartDate;
				$periodEnd = HDate::getMinDateOfWeek($datesWithWeeks, $week, $currentYear)['end_date'];
			} elseif ($currentWeekYear === $lastIndex) {
				$periodStart = HDate::getMinDateOfWeek($datesWithWeeks, $week, $currentYear)['start_date'];
				$periodEnd = $requestEndDate ?? $reportEndDate;
			} else {
				$rangedWeeks = HDate::getMinDateOfWeek($datesWithWeeks, $week, $currentYear);
				$periodStart = $rangedWeeks['start_date'];
				$periodEnd = $rangedWeeks['end_date'];
			}

			$dates[$currentWeekYear] = [
				'start_date' => $periodStart,
				'end_date' => $periodEnd,
			];
		}

		return $dates;
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
	
	
	
	
	
	


	
	
	public function getPastDueCustomerInvoices(string $invoiceType,string $currency , int $companyId , ?string $contractCode = null , ?string $mainFunctionalCurrency = null ){
		$fullClassName = '\App\Models\\'.$invoiceType;

		// Company-wide + viewing the main functional currency tab: show
		// past-due invoices in EVERY currency (their net_balance_in_main_currency
		// column already carries the converted equivalent). Any other tab
		// (a specific foreign currency, or a single-currency contract) keeps
		// the original strict same-currency filter.
		$showAllCurrenciesConverted = ! $contractCode && $mainFunctionalCurrency !== null && $currency === $mainFunctionalCurrency;

		$items  = $fullClassName::where('company_id',$companyId)
		->where('net_balance','>',0)
		->whereIn('invoice_status',['past_due','partially_collected_and_past_due'])
		->when(! $showAllCurrenciesConverted, function($query) use ($currency) {
			$query->where('currency',$currency);
		})
		->where('invoice_due_date','<',now()->format('Y-m-d'))
		->when($contractCode , function($query) use($contractCode) {
			$query->where('contract_code',$contractCode);
		})
		->orderBy('invoice_due_date')
		->get()->toArray() ;
		
		return $items;
	}
	public function getPastDueLoanSchedules(string $currency , int $companyId , ?string $mainFunctionalCurrency = null , ?Collection $foreignExchangeRates = null ){
		$showAllCurrenciesConverted = $mainFunctionalCurrency !== null && $currency === $mainFunctionalCurrency;

		$items  = LoanSchedule::where('loan_schedules.company_id',$companyId)
		->where('remaining','>',0)
		->join('medium_term_loans','medium_term_loans.id','=','loan_schedules.medium_term_loan_id')
		->when(! $showAllCurrenciesConverted, function($query) use ($currency) {
			$query->where('medium_term_loans.currency',$currency);
		})
		->whereIn('loan_schedules.status',['past_due','partially_collected_and_past_due'])
		->where('date','<',now()->format('Y-m-d'))
		->orderBy('date')
		->selectRaw('loan_schedules.*,medium_term_loans.currency,medium_term_loans.name as loan_name')->get();

		// When every currency is included, each row needs its own converted
		// equivalent — there's no net_balance_in_main_currency column here.
		if ($showAllCurrenciesConverted) {
			$items = $items->map(function($item) use ($mainFunctionalCurrency, $companyId, $foreignExchangeRates) {
				$rate = ForeignExchangeRate::getExchangeRateAt(
					(string) $item->currency,
					$mainFunctionalCurrency,
					(string) $item->date,
					$companyId,
					$foreignExchangeRates ?? collect(),
				);
				$item->remaining_in_main_currency = (float) $item->remaining * $rate;
				return $item;
			});
		} else {
			$items = $items->map(function($item) {
				$item->remaining_in_main_currency = (float) $item->remaining;
				return $item;
			});
		}

		return $items->toArray();
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
	public function refreshDueInvoicesAndSettlements(Company $company , Request $request , string $currency , bool $isContract ,?string $contractCode = null  )
	{
		
		
		
	
		$cashflowReportId = $request->get('cashFlowReportId');
		$model  = $cashflowReportId ? CashFlowReport::find($cashflowReportId) : $company;
		$mainFunctionalCurrency = $company->getMainFunctionalCurrency();
		$foreignExchangeRates = ForeignExchangeRate::where('company_id',$company->id)->get();
		// for loans
		if($cashflowReportId && $cashflowReportId > 0){
			$oldReportData = json_decode($model->report_data,true);
			$oldReportData ? extract($oldReportData) : null;
			// for customers 
			$pastDueCustomerInvoices = $this->getPastDueCustomerInvoices('CustomerInvoice',$currency,$company->id,$contractCode,$mainFunctionalCurrency);
			// $excludeIds = $pastDueCustomerInvoices->where('net_balance_until_date','<=',0)->pluck('id')->toArray() ;
			$customerDueInvoices=json_decode(json_encode(DB::table('weekly_cashflow_custom_due_invoices')->where('company_id',$company->id)
			->where('invoice_type','CustomerInvoice')
			->where('cashflow_report_id',$cashflowReportId)
			->where('is_contract',$isContract)
			// ->whereNotIn('invoice_id',$excludeIds)
			->groupBy('week_start_date')->selectRaw('week_start_date,sum(amount) as amount')->get()),true);
		
		// for suppliers 
			$pastDueSupplierInvoices = $this->getPastDueCustomerInvoices('SupplierInvoice',$currency,$company->id,$contractCode,$mainFunctionalCurrency);
			$supplierDueInvoices=json_decode(json_encode(DB::table('weekly_cashflow_custom_due_invoices')->where('company_id',$company->id)
			->where('invoice_type','SupplierInvoice')
			->where('cashflow_report_id',$cashflowReportId)
			->where('is_contract',$isContract)
			// ->whereNotIn('invoice_id',$excludeIds)
			->groupBy('week_start_date')->selectRaw('week_start_date,sum(amount) as amount')->get()),true);
		
			$pastDueInstallments = $this->getPastDueLoanSchedules($currency,$company->id,$mainFunctionalCurrency,$foreignExchangeRates);
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
		// just initialize 
		$allCurrencies = [
			$company->getMainFunctionalCurrency()
		];
		$redirectRouteName= '';
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
