<?php

namespace App\Http\Controllers\NonBankingServices;

use App\Helpers\HArr;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\NonBankingService\Study;
use Illuminate\Support\Facades\DB;
use MathPHP\Finance;

class ValuationController extends Controller
{
    public function index(Company $company, Study $study,$onlyViewVars = false )
    {
		// $study->recalculateCashflowStatement();
		$study->force_yearly = 1;
        // $start = microtime(true);
        $dateIndexWithDate = app('dateIndexWithDate');
        $formattedExpenses = [];
        $formattedResult = [];
        $salesRevenuePerTypes = [];
        $yearWithItsIndexes = $study->getOperationDurationPerYearFromIndexes();
        $monthsWithItsYear = $study->getMonthsWithItsYear($yearWithItsIndexes) ;
        $tableDataFormatted = [];
			$yearIndexWithYear = app('yearIndexWithYear');
        $expenseMainTitlesMapping = getExpenseTypes();
        $loanSchedulePayments = DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('loan_schedule_payments')->selectRaw('portfolio_loan_type,revenue_stream_type,interestAmount,securitization_date_index,endBalance')->where('study_id', $study->id)->get()->toArray();
        $defaultNumericInputClasses = [
            'number-format-decimals'=>0,
            'is-percentage'=>false,
            'classes'=>'repeater-with-collapse-input',
            'formatted-input-classes'=>'custom-input-numeric-width ',
        ];
        $defaultPercentageInputClasses = [
            'classes'=>'',
            'formatted-input-classes'=>'',
            'is-percentage'=>true ,
            'number-format-decimals'=> 2,
        ];
        $defaultClasses = [
            $defaultNumericInputClasses,
            $defaultPercentageInputClasses
        ];
        $orderIndexPerExpenseCategory = [
            // 'sales_revenue'=>0,
            'cost-of-service'=>1 ,
            'gross-profit'=>2 ,
            'other-operation-expense'=>3,
            'marketing-expense'=>4 ,
            'sales-expense'=>5,
            'general-expense'=>6,
            'ebitda'=>7,
            'ecl'=>8,
            'ebit'=>9,
            'financial-expense'=>10,
            'ebt'=>11,
            'corporate-taxes'=>12,
            'net-profit'=>13
        ];
		
		$yearOrMonthsIndexesFromStudy = $study->getYearOrMonthIndexes();
		
$incomeStatement = $study->incomeStatement;
		
		 $formattedDcfMethod['ebit'] =  $incomeStatement ? $incomeStatement->ebit : [];
		
			$years = range(0 , $study->getDurationInYears()-1);
        $balanceSheet = $study->balanceSheet;
		 $yearWithItsMonths=$study->getYearIndexWithItsMonths();

		/**
		 * * start changeInCustomerOutstanding
		 */
		$yearIndexWithLastMonth = HArr::getLastMonthOfYear($yearWithItsMonths);
		$customerOutstandingOpeningBalance=$study->getCustomerReceivableAmount();
		$changeInCustomerOutstanding = HArr::calculateChangeInAfter($balanceSheet->yearly_customer_outstanding,$customerOutstandingOpeningBalance,$yearIndexWithLastMonth);
		$changeInCustomerOutstanding = array_values($changeInCustomerOutstanding);
		
		/**
		 * * end changeInCustomerOutstanding
		 */
		
		
			/**
		 * * start changeIn otherDebtors
		 */
		$yearIndexWithLastMonth = HArr::getLastMonthOfYear($yearWithItsMonths);
		$otherDebtorsOpeningBalance=$study->getTotalOtherDebtors();
		$changeInOtherDebtors = HArr::calculateChangeInAfter($balanceSheet->yearly_other_debtors,$otherDebtorsOpeningBalance,$yearIndexWithLastMonth);
		$changeInOtherDebtors = array_values($changeInOtherDebtors);
		/**
		 * * end changeIn otherDebtors
		 */
		
		/**
		 * * start changeIn portfolio loan outstanding
		 */
		$yearIndexWithLastMonth = HArr::getLastMonthOfYear($yearWithItsMonths);
		$portfolioLoanOutstandingOpeningBalance = DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('supplier_payable_opening_balances')->where('study_id', $study->id)->first();
		$portfolioLoanOutstandingOpeningBalance = $portfolioLoanOutstandingOpeningBalance ? $portfolioLoanOutstandingOpeningBalance->amount : 0 ;
		$changeInPortfolioLoanOutstanding = HArr::calculateChangeInBefore($balanceSheet->yearly_portfolio_loan_outstanding,$portfolioLoanOutstandingOpeningBalance,$yearIndexWithLastMonth);
		$changeInPortfolioLoanOutstanding = array_values($changeInPortfolioLoanOutstanding);
		/**
		 * * end changeIn portfolio loan outstanding
		 */
		
		
			/**
		 * * start changeIn otherCreditors
		 */
		$yearIndexWithLastMonth = HArr::getLastMonthOfYear($yearWithItsMonths);
		$otherCreditorsOpeningBalance=$study->getTotalOtherCreditors();
		$changeInOtherCreditors = HArr::calculateChangeInAfter($balanceSheet->yearly_other_creditors,$otherCreditorsOpeningBalance,$yearIndexWithLastMonth);
		$changeInOtherCreditors = array_values($changeInOtherCreditors);
		
		/**
		 * * end changeIn otherCreditors
		 */
		
		$netChangeInWorkingCapital = HArr::sumAtDates([$changeInCustomerOutstanding,$changeInOtherDebtors,$changeInPortfolioLoanOutstanding,$changeInOtherCreditors],$years);
		
		
		
	
		
		
		
	
		
		
		
		
		
        $cashflowReport = $study->cashflowStatementReport;
        $taxRate = $study->getCorporateTaxesRate() / 100 ;
		$formattedDcfMethod['ebit']=$study->replaceMonthIndexWithYearIndex($formattedDcfMethod['ebit']);
		 $ebit = $formattedDcfMethod['ebit'];
        $formattedDcfMethod['taxes'] = $taxes =  HArr::MultiplyWithNumberIfPositive($formattedDcfMethod['ebit'], $taxRate);
        $formattedDcfMethod['depreciation'] =  $incomeStatement ? $incomeStatement->total_depreciation : [];
		$formattedDcfMethod['depreciation'] = $study->replaceMonthIndexWithYearIndex($formattedDcfMethod['depreciation']);
		$depreciation = $formattedDcfMethod['depreciation'];
        $formattedDcfMethod['net-change-in-working-capital'] = $netChangeInWorkingCapital ;
        // $formattedDcfMethod['net-change-in-working-capital'] = $netChangeInWorkingCapital = $balanceSheet ? $study->replaceMonthIndexWithYearIndex($balanceSheet->net_change_in_working_capital) : [];
		 $studyDates = array_keys($study->getStudyDates()) ;
        $sumKeys = $studyDates;
		
		$fixedAssetPayments = HArr::sumAtDates([$cashflowReport->fixed_asset_payments , $cashflowReport->total_fixed_asset_replacement_costs ],$sumKeys); 
		$yearWithItsMonths=$study->getYearIndexWithItsMonths();
		$fixedAssetPayments = HArr::sumPerYearIndex($fixedAssetPayments,$yearWithItsMonths);
		$fixedAssetPayments = $study->replaceMonthIndexWithYearIndex($fixedAssetPayments);
		
        // $fixedAssetPayments = $study->replaceMonthIndexWithYearIndex($fixedAssetPayments) ;
        $formattedDcfMethod['capex'] = $capex =  $cashflowReport ? $fixedAssetPayments  : [];
        $sum = HArr::sumAtDates([$ebit,$depreciation,$netChangeInWorkingCapital], $years);
        $minus = HArr::sumAtDates([$taxes,$capex], $years);
        $freeCashflow = HArr::subtractAtDates([$sum,$minus], $years);
		
        $formattedDcfMethod['free-cashflow'] = $freeCashflow ;
        $lastValueFreeCashflow = $freeCashflow[array_key_last($freeCashflow)] ??0;
        $perptual = $study->getPerpetualGrowthRate()/100;
        $lastValueFreeCashflow = $lastValueFreeCashflow * (1+$perptual);
        $returnRate = $study->getCostOfEquityRate()/100;
        $total =  0 ;
        $fixedAssetAmounts = [];
		$totalAfterInterest = [];
        // foreach ($this->fixedAssets as $fixedAsset) {
        //     $debitFundingRate = (100-($fixedAsset->equity_funding_rate/100)) ;
        //     $amount = $fixedAsset->getAmount();
        //     $total += 	($amount*$debitFundingRate);
        //     $fixedAssetAmounts[$fixedAsset->id] = $amount*$debitFundingRate ;
        // }
        
        // foreach ($fixedAssetAmounts as $fixedAssetId => &$currentTotal) {
        //     $fixedAsset = FixedAsset::find($fixedAssetId);
        //     if ($total != 0) {
        //         $totalAfterInterest[$fixedAssetId] = ($currentTotal / $total) * ($fixedAsset->interest_rate/100);
                
        //     } else {
        //         $totalAfterInterest[$fixedAssetId] = 0;
        //     }
        // }
        $costOfDebit = array_sum($totalAfterInterest);
        $debitFundingPercentages = $balanceSheet ? (array)$balanceSheet->debit_funding_percentages  : [];
        $equityFundingPercentages = $balanceSheet ? (array)$balanceSheet->equity_funding_percentages  : [1,1,1,1,1];
        $debitFundingPercentages = HArr::MultiplyWithNumber($debitFundingPercentages, $costOfDebit);
        $equityFundingPercentages = HArr::MultiplyWithNumber($equityFundingPercentages, $returnRate);
        $wacc = HArr::sumAtDates([$equityFundingPercentages,$debitFundingPercentages],$years);
        // unset($wacc[array_key_last($wacc)]);
        $lastKeyInWacc  = $wacc[array_key_last($wacc)] ?? 0;
        $terminalValues = [];
        foreach ($years as $index => $yearIndex) {
            $terminalValues[$yearIndex] = 0 ;
            if ($index == count($years)-1) {
                $terminalValues[$yearIndex] = $lastValueFreeCashflow /($lastKeyInWacc-$perptual);
            }
        }
        $formattedDcfMethod['terminal-value'] = $terminalValues ;
        $formattedDcfMethod['free-cashflow-with-terminal'] = $freeCashflowWithTerminal = HArr::sumAtDates([$terminalValues,$freeCashflow],$years) ;
        $newWacc=[];
        $index = 1 ;
        foreach ($wacc as $yearAsIndex => $wacc) {
            $newWacc[$yearAsIndex] = pow(1+$wacc, $index);
            $index++;
        }
        $formattedDcfMethod['discount-factor'] = $newWacc ;
        $formattedDcfMethod['npv'] = [0=>array_sum(HArr::divideTwoArrAtSameIndex($freeCashflowWithTerminal, array_values($newWacc)))] ;

        $formattedDcfMethod['irr'] = [Finance::irr($freeCashflowWithTerminal)*100] ;
		$title  = __('Discounted Cashflow Valuation');
        return view('non_banking_services.income-statement.valuation',[
			'company'=>$company ,
			'study'=>$study , 
			'model'=>$study ,
			'title'=>$title,
			'tableTitle'=>$title,
			'formattedDcfMethod'=>$formattedDcfMethod??[],
			    'studyDates'=>$yearOrMonthsIndexesFromStudy,
				 'yearWithItsIndexes'=>$yearWithItsIndexes,
				 
		] );
        
        
        
    }
    // protected function getViewVars(Company $company, Study $model = null):array
    // {
    //     $actionRoute=isset($model) ? route('update.study', [$company->id , $model->id]) : route('store.financial.planning.study', ['company'=>$company->id]);
    //     return [
    //         'company'=>$company,
    //         'title'=>$company->getName().' ' . __(' Financial Plan'),
    //         'model'=>$model,
    //         'actionRoute'=>$actionRoute,
    //         'navigators' => [],
    //     ];
    // }
    // public function create(Company $company, Request $request)
    // {
    //     return view('financial_planning.study.form', $this->getViewVars($company));
    // }
    // public function store(Company $company, Request $request, Study $study = null)
    // {
    //     $request->merge([
    //         'study_start_date'=>Carbon::make($request->get('study_start_date'))->format('Y-m-d'),
    //         'study_end_date'=>Carbon::make($request->get('study_end_date'))->format('Y-m-d'),
    //         'operation_start_date'=>Carbon::make($request->get('operation_start_date'))->format('Y-m-d'),

            
    //     ]);
    //     $data = $request->except(['_token']) ;
    //     $model = null ;
    //     if (is_null($study)) {
    //         $model = Study::create($data);
    //     } else {
    //         $study->update($data);
    //         $model = $study;
    //     }

    //     /**
    //      * @var Study $model
    //      */
    //     $datesAsStringAndIndex = $model->getDatesAsStringAndIndex();
    //     $studyDates = $model->getStudyDates() ;
    //     $datesAndIndexesHelpers = $model->datesAndIndexesHelpers($studyDates);
    //     $datesIndexWithYearIndex=$datesAndIndexesHelpers['datesIndexWithYearIndex'];
    //     $yearIndexWithYear=$datesAndIndexesHelpers['yearIndexWithYear'];
    //     $dateIndexWithDate=$datesAndIndexesHelpers['dateIndexWithDate'];
    //     $dateWithMonthNumber=$datesAndIndexesHelpers['dateWithMonthNumber'];
    //     $model->updateStudyAndOperationDates($datesAsStringAndIndex, $datesIndexWithYearIndex, $yearIndexWithYear, $dateIndexWithDate, $dateWithMonthNumber);
    //     return response()->json([
    //         'redirectTo'=>route('create.general.assumption', ['company'=>$company->id,'study'=>$model->id])
    //     ]);
    // }
    // public function edit(Company $company, Request $request, Study $study)
    // {
    //     return view('financial_planning.study.form', $this->getViewVars($company, $study));
    // }
    // public function update(Request $request, Company $company, Study $study)
    // {
    //     return $this->store($company, $request, $study);
    // }
    // public function destroy(Request $request, Company $company, Study $study)
    // {
    //     $study->delete();
    //     return redirect()->back()->with('success', __('Study Has Been Deleted Successfully'));
    // }
}
