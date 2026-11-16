<?php

namespace App\ReadyFunctions;

use App\Helpers\HArr;
use App\Helpers\HDate;
use App\Models\Loan;
use App\ReadyFunctions\Date;
use App\Traits\HasMultiBaseLoan;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class CalculateFixedLoanAtBeginningService
{
    
	/**
	 * A loan paid at the beginning of each period, priced with the rate in force in its own month and set again only in
	 * the months the rate changes (the same rule as CalculateFixedLoanAtEndService, phase 4):
	 *  • the installment of the change month is the first at the new rate (paid at the start of the month, it carries
	 *    that month's interest); it used to be the installment after it, one month late;
	 *  • the first installment stays on its date: the grace left is kept and accrues at the new rate (it used to be
	 *    lost, so the client paid from the month after the change);
	 *  • the step counter continues from the installments already paid (it used to start again).
	 */
	public function __calculateBasedOnDiffBaseRates(array $baseRatesMapping, string $loanType, string $loanStartDate, float $loanAmount, float $marginRate, float $tenor, string $installmentPaymentIntervalName, int $installmentPaymentIntervalValue, float $stepUpRate = 0, ?string $stepUpIntervalName = null, float $stepDownRate = 0, ?string $stepDownIntervalName = null, float $gracePeriod  = 0, int $monthIndex = 0, array $datesAsStringAndIndex = [], array $dateWithDateIndex = []):array
    {
        if ($loanAmount <= 0 || $tenor < 1) {
            return [] ;
        }
        // the row of the first installment, the way __calculate places it: the grace in whole installment periods
        $firstInstallmentIndex = $monthIndex + $installmentPaymentIntervalValue * intdiv((int) $gracePeriod, $installmentPaymentIntervalValue);
        $schedule = [];
        $segmentNumber = 0;
		// the rate in force in the loan's month, then only the months the rate changes in
        foreach (HArr::rateChangesFromMonth($baseRatesMapping, $monthIndex, $datesAsStringAndIndex) as $changeIndex => $currentBaseRate) {
            $startIndex = $monthIndex;
            $startDate = $loanStartDate;
            $amount = $loanAmount;
            $remainingTenor = $tenor;
            $remainingGrace = $gracePeriod;
            $installmentsPaid = 0;
            if ($segmentNumber > 0) {
                $startIndex = self::firstRowAtOrAfter($schedule['beginning'] ?? [], $changeIndex);
                $startDate = is_null($startIndex) ? null : ($dateWithDateIndex[$startIndex] ?? null);
                if (is_null($startDate)) {
                    break; // the change comes after the loan's last row (or after the study's dates)
                }
                $amount = (float) $schedule['beginning'][$startIndex];
                $remainingTenor = $tenor - ($startIndex - $monthIndex);
                if ($amount <= 0 || $remainingTenor < $installmentPaymentIntervalValue) {
                    break; // the loan is repaid, nothing is left to re-price
                }
                $remainingGrace = max(0, $firstInstallmentIndex - $startIndex);
                $installmentsPaid = $startIndex <= $firstInstallmentIndex ? 0 : intdiv($startIndex - $firstInstallmentIndex, $installmentPaymentIntervalValue);
            }
            $segment = $this->__calculate($schedule, $segmentNumber, $loanType, $startDate, $amount, $currentBaseRate, $marginRate, $remainingTenor, $installmentPaymentIntervalName, $stepUpRate, $stepUpIntervalName, $stepDownRate, $stepDownIntervalName, $remainingGrace, $startIndex, $installmentsPaid);
            $schedule = $segment['final_result'] ?? $schedule;
            $segmentNumber++;
        }
        unset($schedule['totals']);
		if ($installmentPaymentIntervalName != 'monthly' && $schedule !== []) {
            $schedule = $this->extendPerMonth($schedule, $installmentPaymentIntervalValue);
        }
        return $schedule;
    }

    /** the first row of the schedule at or after $index (null if the schedule ends before it) */
    private static function firstRowAtOrAfter(array $rows, int $index): ?int
    {
        $first = null;
        foreach (array_keys($rows) as $rowIndex) {
            if ($rowIndex >= $index && ($first === null || $rowIndex < $first)) {
                $first = (int) $rowIndex;
            }
        }

        return $first;
    }

    // public function __calculateBasedOnDiffBaseRates( array $baseRatesMapping ,string $loanType, string $loanStartDate, float $loanAmount, float $marginRate, float $tenor, string $installmentPaymentIntervalName,int $installmentPaymentIntervalValue, float $stepUpRate = 0, string $stepUpIntervalName = null, float $stepDownRate = 0, string $stepDownIntervalName = null, float $gracePeriod  = 0,int $monthIndex = 0,array $datesAsStringAndIndex = [],array $dateWithDateIndex = [] ):array
    // {
        
    // 	$currentStartDateAsIndex=$monthIndex ;

    // 	if($loanAmount <= 0){
    // 		return [] ;
    // 	}
    // 	$fixedAtEndResult = [];
    // 	$i = 0 ;
    // 	$previousResult = [];
    // 	$diffInMonths = 0;
    // 	foreach($baseRatesMapping as $currentBaseRateDate => $currentBaseRate){
        
        
    // 		if($i != 0){
    // 			$diffInMonths=Carbon::make($currentBaseRateDate)->diffInMonths($loanStartDate);
    // 			$currentBaseRateDateAsIndex = $datesAsStringAndIndex[$currentBaseRateDate];
    // 			$previousLoopDateAsIndex = $currentBaseRateDateAsIndex-1;
    // 			$currentStartDateAsIndex  = $previousLoopDateAsIndex ;
    // 			$loanStartDate = $dateWithDateIndex[$currentStartDateAsIndex];
    // 			$tenor = $tenor -$diffInMonths+ $installmentPaymentIntervalValue ;
    // 			if($tenor >= 1 ){
    // 				$loanAmount = HArr::getValueOrPrevious($fixedAtEndResult['current_result'][$i-1]['endBalance'], $currentStartDateAsIndex);
    // 			}
    // 		}
    // 			$currentResultArr = [];
    // 			if($tenor >= 1){
    // 				$currentResultArr =$this->__calculate($previousResult,$i,$loanType,$loanStartDate,$loanAmount,$currentBaseRate,$marginRate,$tenor,$installmentPaymentIntervalName,$stepUpRate,$stepUpIntervalName,$stepDownRate,$stepDownIntervalName,$gracePeriod,$currentStartDateAsIndex);
    // 				$previousResult =$currentResultArr['final_result']??[];
    // 				$fixedAtEndResult['current_result'][]= $currentResultArr['result']??[]  ;
    // 				$fixedAtEndResult['final_result']= $currentResultArr['final_result']??[]  ;
    // 				$i++ ;
    // 			}
            
    // 	}
    
    // 	$finalResult = $fixedAtEndResult['final_result']??[] ;
    // 	unset($finalResult['totals']);
    // 	return $finalResult;
    // }
    
    /** $installmentsAlreadyPaid: when the loan is priced again after some installments, the step counter continues from them */
    public function __calculate($previousResult, int $indexOfLoop, string $loanType, string $startDate, float $loanAmount, $baseRate, float $marginRate, float $tenor, string $installmentPaymentIntervalName, float $stepUpRate = 0, ?string $stepUpIntervalName = null, float $stepDownRate = 0, ?string $stepDownIntervalName = null, float $gracePeriod  = 0, $currentStartDateAsIndex=0, int $installmentsAlreadyPaid = 0)
    {
        if ($loanAmount <= 0) {
            return [] ;
        }
        $loanFactors = [];
        $installmentFactors = [];
		$interestFactors=[];
        $datesAsIndexString=HDate::generateDatesBetweenStartDateAndDuration($currentStartDateAsIndex, $startDate, $tenor, $installmentPaymentIntervalName, false);
        $installmentPaymentIntervalValue = $this->getInstallmentPaymentIntervalValue($installmentPaymentIntervalName);
        $datesIndexAndDaysCount =HDate::calculateDaysCountAtBeginning($datesAsIndexString, $installmentPaymentIntervalValue);
        unset($datesAsIndexString[array_key_last($datesAsIndexString)]);
        $datesAsStringIndex = array_flip($datesAsIndexString);
        $currentPricing =  ($baseRate + $marginRate) /100  ;
        $stepRate = Loan::getStepRate($loanType, $stepUpRate, $stepDownRate);
        $stepRate = $stepRate / 100;
        $isWithCapitalization = Loan::isWithCapitalization($loanType);
        $appliedStepName = Loan::getAppliedStepIntervalName($loanType, $stepUpIntervalName, $stepDownIntervalName);
        $appliedStepValue = $this->getAppliedStepIntervalValue($appliedStepName);
        $installmentStartDateAsIndex = $datesAsStringIndex[HDate::getDateAfterIndex($datesAsIndexString, $datesAsStringIndex, $startDate, ($gracePeriod)/$installmentPaymentIntervalValue)];
        $endDateAsIndex = array_key_last($datesAsIndexString);
        $stepFactors = [];
        $currentStepFactorCounterValue = 0;
        $currentAppliedStepCounter = 0 ;
        $installmentsPerStep = $appliedStepValue / $installmentPaymentIntervalValue;
        if ($installmentsAlreadyPaid > 0 && $installmentsPerStep >= 1 && floor($installmentsPerStep) == $installmentsPerStep) {
            $currentStepFactorCounterValue = intdiv($installmentsAlreadyPaid, (int) $installmentsPerStep);
            $currentAppliedStepCounter = $installmentsAlreadyPaid % (int) $installmentsPerStep;
        }
        $currentLoanFactor = $loanAmount;
        $currentInstallmentFactor = 0 ;
        foreach ($datesIndexAndDaysCount as $currentDateAsIndex => $currentDaysCount) {
            
            /**
             * * calculate Interest Loan Factor
             */
            $interestFactors[$currentDateAsIndex]=($currentPricing / 360) * $currentDaysCount;
            
            $currentInterestFactor = $interestFactors[$currentDateAsIndex] ;
            /**
             * * Calculate Loan Factors
             */
            if (!$isWithCapitalization && $currentDateAsIndex < $installmentStartDateAsIndex) {
                $currentLoanFactor = $loanAmount;
            } else {
                $currentLoanFactor = $currentLoanFactor + ($currentLoanFactor * $currentInterestFactor);
            }
            $loanFactors[$currentDateAsIndex] = $currentLoanFactor;
                 
            /**
             * * Calculate Step Factor
             */
            if ($currentDateAsIndex < $installmentStartDateAsIndex) {
                $stepFactors[$currentDateAsIndex]= 0 ;
            } else {
                $currentAppliedStepCounter++ ;
                $stepFactors[$currentDateAsIndex] = $currentStepFactorCounterValue;
                if ($currentAppliedStepCounter == $appliedStepValue/$installmentPaymentIntervalValue) {
                    $currentAppliedStepCounter = 0 ;
                    $currentStepFactorCounterValue++;
                }
            }
                  
            if ($currentDateAsIndex < $installmentStartDateAsIndex) {
                $currentInstallmentFactor =  0 ;
            } else {
                // steps counted from this schedule's first installment (that one is the installment solved below)
                $v = $stepFactors[$currentDateAsIndex] - $stepFactors[$installmentStartDateAsIndex] ;
                $currentInstallmentFactor = ($currentInstallmentFactor + (($currentInstallmentFactor-((pow((1 + $stepRate), ($v))))) * $currentInterestFactor) - (1 * pow((1 + $stepRate), ($v))));
            }
            $installmentFactors[$currentDateAsIndex] = $currentInstallmentFactor;
            /**
             * * Calculate Installment Factor
             */
        
        }
        $installmentAmounts = $this->calculateInstallmentAmount($installmentPaymentIntervalValue, $loanFactors, $installmentFactors, $stepRate, $installmentStartDateAsIndex, $endDateAsIndex, $tenor, $installmentPaymentIntervalValue, $appliedStepValue, $installmentsAlreadyPaid);

        $loanScheduleResult = $this->calculateLoanScheduleResult($installmentPaymentIntervalValue, $datesIndexAndDaysCount, $loanType, $loanAmount, $interestFactors, $installmentAmounts);
        
        $loanScheduleResult['accured_interest'] = [];
        
        if ($indexOfLoop == -1) {
            
            if ($installmentPaymentIntervalName != 'monthly') {
                $loanScheduleResult = $this->extendPerMonth($loanScheduleResult, $installmentPaymentIntervalValue);
            }
            return [
                'final_result'=>$loanScheduleResult,
            ];
        }
        $mergedResult = $indexOfLoop == 0 ? $loanScheduleResult :$previousResult ;
		
        $mergedResult = $loanScheduleResult;
		
		
        
        foreach ($previousResult as $key => $currentArr) {
            if ($key == 'totals') {
                continue;
            }
            $mergedResult[$key]=HArr::mergeTwoAssocArr($previousResult[$key], $loanScheduleResult[$key]);
        }
        return [
            'result'=>$loanScheduleResult ,
            'final_result'=>$mergedResult 
        ];
        
    }

    
    public function getInstallmentPaymentIntervalValue($installmentPayment):int
    {
        switch ($installmentPayment) {
            case 'monthly':
                return 1;
            case 'quarterly':
                return 3;
            case 'semi annually':
                return 6;
			default:
				return 1;
        }
    }

    protected function getAppliedStepIntervalValue($appliedStepIntervalName):int
    {
    
        switch ($appliedStepIntervalName) {
            case 'quarterly':
                return 3;
            case 'semi annually':
                return 6;
            case 'annually':
                return 12;
            default:
                return 12;
        }
    }

    protected function defaultDateFormat():string
    {
        return 'd-m-Y';
    }

    protected function addMonths(string $loanStartDateDay, string $date, int $duration):Carbon
    {
        return Carbon::make((new Date())->addMonths($loanStartDateDay, $date, $duration, 0, 1, 2));
    }

    protected function getDateFormatted(Carbon $date):string
    {
        return $date->format($this->defaultDateFormat());
    }
    

    protected function calculateInstallmentAmount(int $intervalValue, array $loanFactors, array $installmentFactory, float $stepRate, int $installmentStartDateAsIndex, int $endDateAsIndex, float $tenor, int $installmentPaymentIntervalValue, int $appliedStepValue, int $installmentsAlreadyPaid = 0)
    {
    
        $installmentsAmounts = [];

        $loanFactoryAtEndDate = $loanFactors[$endDateAsIndex];
        
        $installmentFactorAtEndDate = $installmentFactory[$endDateAsIndex];

        $installmentAmount = $loanFactoryAtEndDate / ($installmentFactorAtEndDate * -1);

        $installmentsAmounts[$installmentStartDateAsIndex] = $installmentAmount;

        
        for ($i=1 ; $i <= ($tenor / $installmentPaymentIntervalValue) ; $i++) {
            $loopDateAsIndex = $installmentStartDateAsIndex ;
            $stepVal = ($appliedStepValue / $installmentPaymentIntervalValue) ;
            // the step falls on the loan's installment number, also after a re-pricing
            // round 3: a step every installment (step interval = installment interval) is stepVal 1, and anything % 1 is
            // 0: the installment never stepped while the first one was solved for the steps, so the loan ended with a
            // balance (1,000,000 semi-annual over 12 months, step-up 10 % every 6 months: 56,272.19 left)
            if ($i != 1 && ($stepVal <= 1 || (($i + $installmentsAlreadyPaid) % $stepVal) == 1)) {
                $installmentAmount = $installmentAmount * ((pow((1 + $stepRate), 1)));
            } else {
                $installmentAmount = $installmentAmount;
            }
            $installmentsAmounts[$loopDateAsIndex]=$installmentAmount;
            $installmentStartDateAsIndex = $loopDateAsIndex+$intervalValue;
        }
        return $installmentsAmounts;
    }

    protected function calculateLoanScheduleResult(int $intervalValue, array $datesIndexAndDaysCount, string $loanType, float $loanAmount, array $interestFactor, array $installmentAmount)
    {
        $loanScheduleResult = [];
        $loanScheduleResult['totals']['totalSchedulePayment'] = 0;
        $loanScheduleResult['totals']['totalPrincipleAmount'] = 0;
        $loanScheduleResult['totals']['totalInterestAmount'] = 0;
        $isWithoutCapitalization =  Loan::isWithoutCapitalization($loanType);
        $firstLoop = true ;
        foreach ($datesIndexAndDaysCount as $dateAsIndex => $currentDaysCount) {
            $previousDate = $dateAsIndex-$intervalValue;
            $i = $dateAsIndex ;
            $loanScheduleResult['beginning'][$i] =  $firstLoop ? $loanAmount : $loanScheduleResult['endBalance'][$previousDate]??0;
            $currentInstallmentAmount = $installmentAmount[$i] ?? 0;
            $loanScheduleResult['interestAmount'][$i] = ($loanScheduleResult['beginning'][$i] -$currentInstallmentAmount) *   $interestFactor[$i];
            $loanScheduleResult['totals']['totalInterestAmount'] += $loanScheduleResult['interestAmount'][$i];
            $installmentAmountAtIndex =$installmentAmount[$i] ?? 0;
            $loanScheduleResult['schedulePayment'][$i] = $isWithoutCapitalization && $installmentAmountAtIndex == 0 ? $loanScheduleResult['interestAmount'][$i] : $installmentAmountAtIndex;
            $loanScheduleResult['totals']['totalSchedulePayment'] = $loanScheduleResult['totals']['totalSchedulePayment'] + $loanScheduleResult['schedulePayment'][$i];
            $loanScheduleResult['principleAmount'][$i] = $loanScheduleResult['schedulePayment'][$i] - $loanScheduleResult['interestAmount'][$i];
            $loanScheduleResult['totals']['totalPrincipleAmount'] += $loanScheduleResult['principleAmount'][$i];
            $loanScheduleResult['endBalance'][$i] = $loanScheduleResult['beginning'][$i]  + $loanScheduleResult['interestAmount'][$i] -$loanScheduleResult['schedulePayment'][$i];
            $loanScheduleResult['endBalance'][$i] = $loanScheduleResult['endBalance'][$i] < 1 && $loanScheduleResult['endBalance'][$i] > -1 ? 0 : $loanScheduleResult['endBalance'][$i];
            $firstLoop = false ;
        }
        return $loanScheduleResult;
    }
    
    protected function extendPerMonth(array $loanScheduleResult, $installmentPaymentIntervalValue):array
    {
        $result = [];
        $startDateAsIndex = array_key_first($loanScheduleResult['endBalance']??[]);
        $endDateAsIndex = array_key_last($loanScheduleResult['endBalance']??[]);
        $previousBeginningBalance = Arr::first($loanScheduleResult['beginning']??[]) ;
        $result['beginning'] = [];
        $result['endBalance'] = [];
        // $currentInterestAmount = $loanScheduleResult['schedulePayment'][$currentDateAsIndex];
        for ($currentDateAsIndex  =$startDateAsIndex ; $currentDateAsIndex <=$endDateAsIndex ; $currentDateAsIndex++) {
            $currentInterestAmount = $loanScheduleResult['interestAmount'][$currentDateAsIndex]??0;
            if ($currentInterestAmount > 0) {
                for ($i = $currentDateAsIndex ; $i < $currentDateAsIndex + $installmentPaymentIntervalValue ; $i++) {
                    $result['interestAmount'][$i] = $currentInterestAmount / $installmentPaymentIntervalValue;
                }
            }
            $previousEndBalanceValue = $result['endBalance'][$currentDateAsIndex-1]??0;
            $currentEndBalance=$loanScheduleResult['endBalance'][$currentDateAsIndex]??null ;
            $result['schedulePayment'][$currentDateAsIndex] = $loanScheduleResult['schedulePayment'][$currentDateAsIndex]??0;
            $result['principleAmount'][$currentDateAsIndex] = $loanScheduleResult['principleAmount'][$currentDateAsIndex]??0;
            //    $result['no_securitization'][$currentDateAsIndex] = $loanScheduleResult['no_securitization'][$currentDateAsIndex]??0;
            $result['endBalance'][$currentDateAsIndex] = isset($loanScheduleResult['endBalance'][$currentDateAsIndex]) ? $currentEndBalance :$previousEndBalanceValue;
            $result['beginning'][$currentDateAsIndex] = $previousBeginningBalance ;
            $previousBeginningBalance = $result['endBalance'][$currentDateAsIndex];
        }
        $currentInterestAmountArr = $result['interestAmount']??[];
        ksort($currentInterestAmountArr);
        $dateAsIndexes = array_keys($result['beginning']);
        if (app()->bound('dateIndexWithDate')) {
            $result['accured_interest']=Loan::calculateSettlementStatement($dateAsIndexes, $loanScheduleResult['interestAmount'], $result['interestAmount']??[], 0, false, true);
        }
        $result['interestAmount'] = $currentInterestAmountArr ;
        return $result;
    }
    
    


}
