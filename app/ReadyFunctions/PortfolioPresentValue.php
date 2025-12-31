<?php
namespace App\ReadyFunctions;

use App\Helpers\HArr;
use App\Models\NonBankingService\Study;
use Illuminate\Support\Facades\DB;

class PortfolioPresentValue
{
    
    public function calculate(string $revenueStreamCategoryId , array $monthlyStudyOccurrenceDates, Study $study, array $dateIndexWithDate, array $portfolioLoanFundingRatesPerMonths, array $operationDurationPerYearFromIndexes, int $tenorInYears, array $startFromPerYear, array $frequencyPerYear, array $portfolioMortgageTransactionAmountsPerYears, array $cbeLendingRatesPerMonths, float $marginRate, array $bankMarginRates, int $companyId, int $studyId, int $portfolioMortgageCategoryId):array
    {
		
		$operationDates = range($study->getOperationStartDateAsIndex(), $study->getStudyEndDateAsIndex());
        DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('loan_schedule_payments')->where('study_id', $studyId)->where('revenue_stream_type', Study::PORTFOLIO_MORTGAGE)->where('revenue_stream_id', $portfolioMortgageCategoryId)->delete();
        $portfolioMortgageLoanSchedulePayments = [];
        $bankPortfolioLoans=[];
        $currentUnearnedInterestStatement = [];
        $calculateFixedLoanAtEndService = new CalculateFixedLoanAtEndService;
        $monthlyAmounts=[];
        $loanType = 'normal' ;
        $tenorInMonths = $tenorInYears *12;
        $installmentPaymentIntervalName='monthly';
        $accumulatedMonthsAmountsDueDates = [];
        $isMonthlyStudy = $study->isMonthlyStudy() ;
        $occurrenceDates =  $isMonthlyStudy ? $monthlyStudyOccurrenceDates :   $this->calculateOccurrenceDates($operationDurationPerYearFromIndexes, $startFromPerYear, $frequencyPerYear);
        foreach ($occurrenceDates as $currentYearIndex=>$occurrenceIndexesAndDates) {
            $currentYearAmount =  $portfolioMortgageTransactionAmountsPerYears[$currentYearIndex]??0;
              
            foreach ($occurrenceIndexesAndDates as $currentOccurrenceMonthIndex) {
                $currentYearAmount = $isMonthlyStudy ? $portfolioMortgageTransactionAmountsPerYears[$currentOccurrenceMonthIndex] : $currentYearAmount;
                $monthlyAmounts[$currentOccurrenceMonthIndex] = $currentYearAmount;
            }
            
        }
        $totalPortfoliosMortgageEndBalances = [];
        $portfolioInterestAmounts =[];
		$totalInterests = [];
		$totalBankInterests = [];
		$totalSchedulePayments = [];
		$totalBankSchedulePayments = [];
        $yearWithItsMonths=$study->getYearIndexWithItsMonths();
	
        $totalMonthlyAmountsPerYears = HArr::sumPerYearIndex($monthlyAmounts, $yearWithItsMonths);
         // $originalMonthlyAmounts = $monthlyAmounts;
        foreach ($monthlyAmounts as $currentOccurrenceMonthIndex => &$currentOccurrenceAvgAmount) {
            if ($currentOccurrenceAvgAmount == 0) {
                continue ;
            }
        
            $totalNetPresentValue = 0 ;
            $totalInterestAmount= 0;
            $schedulePaymentAmount = $currentOccurrenceAvgAmount  / $tenorInMonths ;
            $currentBaseRate = $cbeLendingRatesPerMonths[$currentOccurrenceMonthIndex] ;
            $currentPricingAtOccurrenceIndex = ($currentBaseRate + $marginRate) / 100;
            $currentMonthlyInterest =  $currentPricingAtOccurrenceIndex / 12  ;
            $isFirstLoop = true ;
            for ($i = 0 ; $i<= $tenorInMonths ; $i++) {
                $currentMonthsCount = $i ;
                $currentSchedulePaymentAmount = $isFirstLoop ? 0 :  $schedulePaymentAmount;
                    
                $currentNetPresetValue = $currentSchedulePaymentAmount / pow(1+$currentMonthlyInterest, $currentMonthsCount);
                $currentInterestAmount = $currentSchedulePaymentAmount -  $currentNetPresetValue ;
                $currentPrincipleAmount = $currentSchedulePaymentAmount - $currentInterestAmount ;
                $endBalance = $currentOccurrenceAvgAmount - $currentSchedulePaymentAmount;
                $totalNetPresentValue += $currentNetPresetValue;
                $portfolioMortgageLoanSchedulePayments[$currentOccurrenceMonthIndex]['beginning'][$i+$currentOccurrenceMonthIndex] = $currentOccurrenceAvgAmount ;
                $portfolioMortgageLoanSchedulePayments[$currentOccurrenceMonthIndex]['interestAmount'][$i+$currentOccurrenceMonthIndex] = $currentInterestAmount ;
                $portfolioInterestAmounts[$currentOccurrenceMonthIndex][$i+$currentOccurrenceMonthIndex] = $currentInterestAmount;
                $totalInterestAmount+= $currentInterestAmount;
                $portfolioMortgageLoanSchedulePayments[$currentOccurrenceMonthIndex]['schedulePayment'][$i+$currentOccurrenceMonthIndex] = $currentSchedulePaymentAmount ;
                $portfolioMortgageLoanSchedulePayments[$currentOccurrenceMonthIndex]['principleAmount'][$i+$currentOccurrenceMonthIndex] = $currentPrincipleAmount ;
                $portfolioMortgageLoanSchedulePayments[$currentOccurrenceMonthIndex]['endBalance'][$i+$currentOccurrenceMonthIndex] = $endBalance ;
                $totalPortfoliosMortgageEndBalances[$i+$currentOccurrenceMonthIndex] = isset($totalPortfoliosMortgageEndBalances[$i+$currentOccurrenceMonthIndex]) ? $totalPortfoliosMortgageEndBalances[$i+$currentOccurrenceMonthIndex] + $endBalance : $endBalance;
                $portfolioMortgageLoanSchedulePayments[$currentOccurrenceMonthIndex]['revenue_stream_type'] = Study::PORTFOLIO_MORTGAGE ;
                $portfolioMortgageLoanSchedulePayments[$currentOccurrenceMonthIndex]['revenue_stream_category_id'] = $revenueStreamCategoryId ;
                $portfolioMortgageLoanSchedulePayments[$currentOccurrenceMonthIndex]['portfolio_loan_type'] = 'portfolio' ;
                $portfolioMortgageLoanSchedulePayments[$currentOccurrenceMonthIndex]['revenue_stream_id'] = $portfolioMortgageCategoryId ;
                $portfolioMortgageLoanSchedulePayments[$currentOccurrenceMonthIndex]['study_id'] = $studyId ;
                $portfolioMortgageLoanSchedulePayments[$currentOccurrenceMonthIndex]['company_id'] = $companyId ;
                $portfolioMortgageLoanSchedulePayments[$currentOccurrenceMonthIndex]['month_as_index'] = $i+$currentOccurrenceMonthIndex ;
				
                $isFirstLoop = false ;
                $currentOccurrenceAvgAmount = $endBalance;
            }
            $portfolioLoanFundingRatesAtOccurrenceMonthIndex = $portfolioLoanFundingRatesPerMonths[$currentOccurrenceMonthIndex] / 100;
            $accumulatedMonthsAmountsDueDates[$currentOccurrenceMonthIndex]['net_present_value'] = $totalNetPresentValue;
            $accumulatedMonthsAmountsDueDates[$currentOccurrenceMonthIndex]['bank_loan_amount'] = $totalNetPresentValue * $portfolioLoanFundingRatesAtOccurrenceMonthIndex;
            $accumulatedMonthsAmountsDueDates[$currentOccurrenceMonthIndex]['unearned_interest'] = $totalInterestAmount;
            $accumulatedMonthsAmountsDueDates[$currentOccurrenceMonthIndex]['base_rate'] = $currentBaseRate ;
                    
        }
        $this->calculateMonthlyAmounts($revenueStreamCategoryId,$portfolioInterestAmounts, $bankMarginRates, $tenorInMonths, $installmentPaymentIntervalName, $loanType, $dateIndexWithDate, $currentUnearnedInterestStatement, $accumulatedMonthsAmountsDueDates, $bankPortfolioLoans, $calculateFixedLoanAtEndService, $portfolioMortgageCategoryId, $studyId, $companyId,$totalBankInterests,$totalBankSchedulePayments,$operationDates);
        foreach ($portfolioMortgageLoanSchedulePayments as $occurrenceDate => &$portfolioMortgageLoanSchedulePayment) {
			$totalInterests= HArr::sumAtDates([$totalInterests,$portfolioMortgageLoanSchedulePayment['interestAmount']??[]],$operationDates);
			$totalSchedulePayments = HArr::sumAtDates([$totalSchedulePayments,$portfolioMortgageLoanSchedulePayment['schedulePayment']??[]],$operationDates);
            foreach ($portfolioMortgageLoanSchedulePayment as $key => &$value) {
                if (is_array($value)) {
                    $value = json_encode($value);
                }
            }
        }
        DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('loan_schedule_payments')->insert(array_values($portfolioMortgageLoanSchedulePayments));
        DB::connection(NON_BANKING_SERVICE_CONNECTION_NAME)->table('loan_schedule_payments')->insert($bankPortfolioLoans);
		
		
		
		
        return [
			'totalInterests'=>$totalInterests,
			'totalBankInterests'=>$totalBankInterests,
            'occurrence_dates'=>$occurrenceDates,
            'statement'=>$accumulatedMonthsAmountsDueDates,
            'portfolio_mortgage_unearned_interest_statement'=>$currentUnearnedInterestStatement,
        //    'loan_amounts'=>$originalMonthlyAmounts,
            'total_monthly_amounts_per_years'=>$totalMonthlyAmountsPerYears,
			'totalSchedulePayments'=>$totalSchedulePayments,
			'totalBankSchedulePayments'=>$totalBankSchedulePayments
        ];
    
    }
    
    
    protected function calculateOccurrenceDates(array $operationDurationPerYearFromIndexes, array $startFromPerYear, array $frequencyPerYear)
    {
        $occurrenceDates = [];
        foreach ($operationDurationPerYearFromIndexes as $currentYearIndex => $months) {
            $currentStartFrom = $startFromPerYear[$currentYearIndex];
            $currentFrequency = $frequencyPerYear[$currentYearIndex];
            $lastMonthIndexInCurrentYear =  array_key_last($months);
            if ($currentFrequency == 0) {
                $occurrenceDates[$currentYearIndex][] = $currentStartFrom;
            } else {
                for ($i = $currentStartFrom  ; $i <=$lastMonthIndexInCurrentYear ; $i += $currentFrequency) {
                    $occurrenceDates[$currentYearIndex][] = $i ;
                }
            }
        }
        return $occurrenceDates;
            
    }
    
    
    protected function calculateMonthlyAmounts(string $revenueStreamCategoryId , array $portfolioInterestAmounts, array $bankMarginRates, $tenorInMonths, $installmentPaymentIntervalName, string $loanType, array $dateIndexWithDate, array &$currentUnearnedInterestStatement, array &$accumulatedMonthsAmountsDueDates, array &$bankPortfolioLoans, CalculateFixedLoanAtEndService $calculateFixedLoanAtEndService, int $portfolioMortgageCategoryId, int $studyId, int $companyId  , array &$totalBankInterests,array &$totalBankSchedulePayments,array $operationDates):void
    {
        foreach ($accumulatedMonthsAmountsDueDates as $currentOccurrenceMonthIndex => $portfolioMortgageLoanArray) {
            $currentBankMarginRate = $bankMarginRates[$currentOccurrenceMonthIndex]??0;
            $currentLoanDateAsString = $dateIndexWithDate[$currentOccurrenceMonthIndex];
            $currentBankLoanAmount = $portfolioMortgageLoanArray['bank_loan_amount'];
            $currentBaseRate = $portfolioMortgageLoanArray['base_rate'];
            $currentUnearnedInterest = $portfolioMortgageLoanArray['unearned_interest'];
            $currentDaysCount = 30 ;
            $bankLoanAmounts[$currentOccurrenceMonthIndex]=$calculateFixedLoanAtEndService->__calculate([], -1, $loanType, $currentLoanDateAsString, $currentBankLoanAmount, $currentBaseRate, $currentBankMarginRate, $tenorInMonths, $installmentPaymentIntervalName, 0, null, 0, null, 0, $currentOccurrenceMonthIndex, $currentDaysCount)['final_result']??[];
            $bankLoanAmountsFormatted=$bankLoanAmounts[$currentOccurrenceMonthIndex];
            if (count($bankLoanAmountsFormatted)) {
                $bankLoanAmountsFormatted['study_id'] = $studyId ;
                $bankLoanAmountsFormatted['company_id'] = $companyId ;
                $bankLoanAmountsFormatted['month_as_index'] = $currentOccurrenceMonthIndex ;
                $bankLoanAmountsFormatted['revenue_stream_id'] =$portfolioMortgageCategoryId ;
                $bankLoanAmountsFormatted['revenue_stream_category_id'] =$revenueStreamCategoryId ;
                $bankLoanAmountsFormatted['portfolio_loan_type'] ='bank_portfolio';
                $bankLoanAmountsFormatted['revenue_stream_type'] =Study::PORTFOLIO_MORTGAGE;
                    $totalBankInterests= HArr::sumAtDates([$totalBankInterests,$bankLoanAmountsFormatted['interestAmount']??[]],$operationDates);
                    $totalBankSchedulePayments= HArr::sumAtDates([$totalBankSchedulePayments,$bankLoanAmountsFormatted['schedulePayment']??[]],$operationDates);
                $bankPortfolioLoans[]=collect($bankLoanAmountsFormatted)->map(function ($item, $keyName) {
                        
                    if (is_array($item)) {
                        return json_encode($item);
                    }
                    return $item;
                })->toArray();
            }
            $currentEndUnearnedBeginningBalance = 0 ;
            foreach ($portfolioInterestAmounts[$currentOccurrenceMonthIndex] as $currentMonth => $currentInterestAmount) {
                $currentEndUnearnedEndBalance = $currentEndUnearnedBeginningBalance + $currentInterestAmount - $currentUnearnedInterest;
                $currentUnearnedInterestStatement[$currentOccurrenceMonthIndex][$currentMonth]['beginning_balance'] = $currentEndUnearnedBeginningBalance;
                $currentUnearnedInterestStatement[$currentOccurrenceMonthIndex][$currentMonth]['interest_amount'] = $currentInterestAmount;
                $currentUnearnedInterestStatement[$currentOccurrenceMonthIndex][$currentMonth]['unearned_interest'] = $currentUnearnedInterest;
                $currentUnearnedInterestStatement[$currentOccurrenceMonthIndex][$currentMonth]['end_balance'] = $currentEndUnearnedEndBalance < 1 && $currentEndUnearnedEndBalance > -1 ? 0 : $currentEndUnearnedEndBalance ;
                $currentEndUnearnedBeginningBalance = $currentEndUnearnedEndBalance;
                $currentUnearnedInterest=0;
            }
        }
        
    }
}
