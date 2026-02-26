<?php
namespace App\ReadyFunctions;

use App\Helpers\HArr;
use App\Models\NonBankingService\GeneralAndReserveAssumption;
use App\Models\NonBankingService\Study;
use Illuminate\Support\Facades\DB;

class PortfolioPresentValue
{
    
    public function calculate(string $revenueStreamCategoryId , array $monthlyStudyOccurrenceDates, Study $study, array $dateIndexWithDate, array $portfolioLoanFundingRatesPerMonths, array $operationDurationPerYearFromIndexes, int $tenorInYears, array $startFromPerYear, array $frequencyPerYear, array $portfolioMortgageTransactionAmountsPerYears, array $cbeLendingRatesPerMonths, float $marginRate, array $bankMarginRates, int $companyId, int $studyId, int $portfolioMortgageCategoryId):array
    {
		
		$operationDates = range($study->getOperationStartDateAsIndex(), $study->getStudyEndDateAsIndex());
        $portfolioMortgageLoanSchedulePayments = [];
        $bankPortfolioLoans=[];
        $currentUnearnedInterestStatement = [];
        $calculateFixedLoanAtEndService = new CalculateFixedLoanAtEndService;
        $monthlyAmounts=[];
        $loanType = 'normal' ;
        $tenorInMonths = $tenorInYears *12;
        $installmentPaymentIntervalName='monthly';
        $accumulatedMonthsAmountsDueDates = [];
        $occurrenceDates =  $monthlyStudyOccurrenceDates ;
	
		$monthlyAmounts = $portfolioMortgageTransactionAmountsPerYears;
        $totalPortfoliosMortgageEndBalances = [];
        $portfolioInterestAmounts =[];
		$totalInterests = [];
		$totalBankInterests = [];
		$totalSchedulePayments = [];
		$totalBankSchedulePayments = [];
		$interestRecognition = [];

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
			$monthlyRecognitionInterests=  [];
            for ($i = 0 ; $i<= $tenorInMonths ; $i++) {
                $currentMonthsCount = $i ;
                $currentSchedulePaymentAmount = $isFirstLoop ? 0 :  $schedulePaymentAmount;
                    
                $currentNetPresetValue = $currentSchedulePaymentAmount / pow(1+$currentMonthlyInterest, $currentMonthsCount);
				// $monthlyRecognitionInterest = $currentNetPresetValue * $currentMonthlyInterest;
				
				// $monthlyRecognitionInterests[$i] = $monthlyRecognitionInterest;
				// $currentNetPresetValue = $currentNetPresetValue + $monthlyRecognitionInterest;
				
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

			$portfolioMortgageLoanSchedulePayments[$currentOccurrenceMonthIndex]['interestAmount'] = $this->calculateInterestRecognition($portfolioMortgageLoanSchedulePayments[$currentOccurrenceMonthIndex]['interestAmount']??[]);
	
			$interestRecognition[$currentOccurrenceMonthIndex] = $portfolioMortgageLoanSchedulePayments[$currentOccurrenceMonthIndex]['interestAmount']??[];
			
			
            $portfolioLoanFundingRatesAtOccurrenceMonthIndex = $portfolioLoanFundingRatesPerMonths[$currentOccurrenceMonthIndex] / 100;
            $accumulatedMonthsAmountsDueDates[$currentOccurrenceMonthIndex]['net_present_value'] = $totalNetPresentValue;
            $accumulatedMonthsAmountsDueDates[$currentOccurrenceMonthIndex]['bank_loan_amount'] = $totalNetPresentValue * $portfolioLoanFundingRatesAtOccurrenceMonthIndex;
            $accumulatedMonthsAmountsDueDates[$currentOccurrenceMonthIndex]['unearned_interest'] = $totalInterestAmount;
            $accumulatedMonthsAmountsDueDates[$currentOccurrenceMonthIndex]['base_rate'] = $currentBaseRate ;
                    
        }
		
        $this->calculateMonthlyAmounts($study,$revenueStreamCategoryId,$interestRecognition, $bankMarginRates, $tenorInMonths, $installmentPaymentIntervalName, $loanType, $dateIndexWithDate, $currentUnearnedInterestStatement, $accumulatedMonthsAmountsDueDates, $bankPortfolioLoans, $calculateFixedLoanAtEndService, $portfolioMortgageCategoryId, $studyId, $companyId,$totalBankInterests,$totalBankSchedulePayments,$operationDates);
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
        //    'total_monthly_amounts_per_years'=>$totalMonthlyAmountsPerYears,
			'totalSchedulePayments'=>$totalSchedulePayments,
			'totalBankSchedulePayments'=>$totalBankSchedulePayments
        ];
    
    }
    
    protected function calculateInterestRecognition(array $interestAmounts):array{
		$resultFormatted = [];
	//	$index=0;
		$firstDateAsIndex =array_key_first($interestAmounts);
		foreach($interestAmounts as $currentDateAsIndex => $interestAmount){
			$divider = $currentDateAsIndex - $firstDateAsIndex;
			$amount = $divider ? $interestAmount /$divider  : 0;
			$resultFormatted[$firstDateAsIndex]=0;
			for($i = $currentDateAsIndex ; $i > $firstDateAsIndex; $i--  ){
				
				$resultFormatted[$i] = ($resultFormatted[$i]??0) + $amount;
	
			}

		}
	
		return $resultFormatted;
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
    
    
    protected function calculateMonthlyAmounts(Study $study,string $revenueStreamCategoryId , array $interestRecognition, array $bankMarginRates, $tenorInMonths, $installmentPaymentIntervalName, string $loanType, array $dateIndexWithDate, array &$currentUnearnedInterestStatement, array &$accumulatedMonthsAmountsDueDates, array &$bankPortfolioLoans, CalculateFixedLoanAtEndService $calculateFixedLoanAtEndService, int $portfolioMortgageCategoryId, int $studyId, int $companyId  , array &$totalBankInterests,array &$totalBankSchedulePayments,array $operationDates):void
    {
        foreach ($accumulatedMonthsAmountsDueDates as $currentOccurrenceMonthIndex => $portfolioMortgageLoanArray) {
            $currentBankMarginRate = $bankMarginRates[$currentOccurrenceMonthIndex]??0;
            $currentLoanDateAsString = $dateIndexWithDate[$currentOccurrenceMonthIndex];
            $currentBankLoanAmount = $portfolioMortgageLoanArray['bank_loan_amount'];
	
            $currentBaseRate = $portfolioMortgageLoanArray['base_rate'];
            $currentUnearnedInterest = $portfolioMortgageLoanArray['unearned_interest'];
            $currentDaysCount = 30 ;
			$baseRatesMapping = $study->generalAndReserveAssumption->getBaseRatesPerMonths(GeneralAndReserveAssumption::MORTGAGE_BANK_LENDING_MARGIN_RATE);
			if(is_array($baseRatesMapping)){
				$dateWithDateIndex = $study->getDateWithDateIndex();
				$installmentPaymentIntervalValue = $calculateFixedLoanAtEndService->getInstallmentPaymentIntervalValue($installmentPaymentIntervalName);
				$bankLoanAmounts[$currentOccurrenceMonthIndex]=$calculateFixedLoanAtEndService->__calculateBasedOnDiffBaseRates($baseRatesMapping,$loanType, $currentLoanDateAsString, $currentBankLoanAmount, $currentBankMarginRate,$tenorInMonths, $installmentPaymentIntervalName, $installmentPaymentIntervalValue, 0, null, 0, null, 0, $currentOccurrenceMonthIndex, $dateWithDateIndex, $dateIndexWithDate)??[];
			}else{
				$bankLoanAmounts[$currentOccurrenceMonthIndex]=$calculateFixedLoanAtEndService->__calculate([], -1, $loanType, $currentLoanDateAsString, $currentBankLoanAmount, $currentBaseRate, $currentBankMarginRate, $tenorInMonths, $installmentPaymentIntervalName, 0, null, 0, null, 0, $currentOccurrenceMonthIndex, $currentDaysCount)['final_result']??[];
			}
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

            foreach ($interestRecognition[$currentOccurrenceMonthIndex] as $currentMonth => $currentInterestAmount) {
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
